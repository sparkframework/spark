<?php

namespace Spark\Core\Command;

use Silex\Application;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AppInfo extends Command
{
    protected $application;

    function __construct(Application $app)
    {
        $this->application = $app;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('app:info')
            ->setDescription('Displays information about the application');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $app = $this->application;

        $skeletonVersion = trim(file_get_contents($app['spark.root'] . '/.app_version'));
        $frameworkVersion = trim(file_get_contents($app['spark.root'] . '/.spark_version'));

        $availableSkeletonVersion = \Spark\Spark::SKELETON_VERSION;

        $output->writeln("Skeleton Version: <info>$skeletonVersion</info>");
        $output->writeln("Framework Version: <info>$frameworkVersion</info>");

        if ($skeletonVersion < $availableSkeletonVersion) {
            $output->writeln("<info>Upgrade available.</info> Run <info>spark upgrade</info>.");
        }
    }
}
