<?php

namespace Spark\Core\Command;

use Silex\Application;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Generate
{
    protected $application;

    function __construct(Application $app)
    {
        $this->application = $app;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('generate')
            ->setDescription('Generates application classes, like Controllers')
            ->setArgument('type', InputArgument::REQUIRED, 'Type of the thing you want to create')
            ->setArgument('name', InputArgument::REQUIRED, 'Name of the artifact')
            ->setArgument('options', InputArgument::OPTIONAL, 'Generator options as key:value');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    }
}
