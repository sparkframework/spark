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

    protected function migrate($version, $description, callable $callback)
    {
        $this->migrations[$version][] = [$description, $callback];
    }

    protected function configure()
    {
        $this->setName('upgrade')
             ->setDescription('Upgrades the application to the latest framework version');

        $this->migrate(2, "Add 'data' directory", function() {
            is_dir('data') ?: mkdir('data', 0777);
        });

        $this->migrate(2, "Add 'app/assets/images' directory", function() {
            is_dir('app/assets/images') ?: mkdir('app/assets/images', 0755, true);
        });

        $this->migrate(3, "Add .htaccess", function() {
            $templateDir = __DIR__ . '/../../../../res';
            copy("$templateDir/templates/public/.htaccess", "public/.htaccess");
        });

        $this->migrate(3, "Add test build configuration", function() {
            
        });
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $dialog = $this->getHelperSet()->get('dialog');

        $question = sprintf('<question>Do you really want to upgrade to version %s?</question> (y/n) > ', \Spark\Spark::VERSION);

        if (!$dialog->askConfirmation($output, $question, false)) {
            return;
        }

        $currentVersion = @file_get_contents('.app_version') ?: 0;

        $migrations = array_filter(
            array_keys($this->migrations),
            function($version) use ($currentVersion) {
                return $version > $currentVersion;
            }
        );

        foreach ($migrations as $version) {
            foreach ($this->migrations[$version] as $migration) {
                list($description, $callback) = $migration;

                $output->writeln("Applying: $description");
                $callback();
            }
        }

        file_put_contents('.app_version', \Spark\Spark::SKELETON_VERSION);
        file_put_contents('.spark_version', \Spark\Spark::VERSION);
    }
}

