<?php

namespace Spark\Core\Command;

use Silex\Application;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Generate extends Command
{
    protected $application;

    function __construct(Application $app)
    {
        $this->application = $app;
    }

    protected function configure()
    {
        $this->setName('generate')
            ->setDescription('Generates application classes, like Controllers')
            ->addArgument('type', InputArgument::REQUIRED, 'Type of the thing you want to create')
            ->addArgument('name', InputArgument::REQUIRED, 'Name of the artifact')
            ->addArgument('options', InputArgument::OPTIONAL, 'Generator options as key:value');

        parent::configure();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('foo');
    }
}
