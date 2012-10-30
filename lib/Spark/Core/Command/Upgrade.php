<?php

namespace Spark\Core\Command;

use Silex\Application;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Upgrade extends Command
{
    protected $migrations = [];

    protected function migrate($version, callable $callback)
    {
        $this->migrations[$version] = $callback;
    }

    protected function configure()
    {
        $this->setName('upgrade')
            ->setDescription('Upgrades the application to the latest framework version');

        $this->migrate(2, function() {
            is_dir('data') ?: mkdir('data', 0777);
            is_dir('app/assets/images') ?: mkdir('app/assets/images', 0755, true);
        });
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dialog = $this->getHelperSet()->get('dialog');

        if (!$dialog->askConfirmation($output, '<question>Do you really want to upgrade?</question>', false)) {
            return;
        }

        $currentVersion = @file_get_contents('.app_version') ?: 0;

        $migrations = array_filter(
            array_keys($this->migrations),
            function($version) use ($currentVersion) {
                return $version > $currentVersion;
            }
        );

        foreach ($migrations as $v) {
            $callback = $this->migrations[$v];
            $callback();
        }

        file_put_contents('.app_version', $v);
        file_put_contents('.spark_version', \Spark\Spark::VERSION);
    }
}

