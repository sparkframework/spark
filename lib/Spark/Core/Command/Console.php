<?php

namespace Spark\Core\Command;

use Silex\Application;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Boris\Boris;

class Console extends Command
{
    protected $application;

    function __construct(Application $app)
    {
        $this->application = $app;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('console')
            ->setDescription('Runs a console with the applicatio loaded');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $boris = new Boris('spark> ');
        $boris->setLocal(array(
            'app' => $this->application
        ));

        $boris->start();
    }
}
