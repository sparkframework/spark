<?php

namespace Spark\Core\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateApplication
{
    protected function configure()
    {
        $this->setName('create:application')
            ->setDescription('Creates a new application')
            ->setArgument('application_name', InputArgument::REQUIRED, 'Name of the application');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
    }
}
