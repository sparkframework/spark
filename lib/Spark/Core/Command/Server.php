<?php

namespace Spark\Core\Command;

use Silex\Application;

use Spark\Core\DevelopmentServer;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Server extends Command
{
    protected $application;

    function __construct(Application $app)
    {
        $this->application = $app;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('server')
            ->setDescription('Run a development server')
            ->addArgument('host', InputArgument::OPTIONAL, 'Host name of the server (default: localhost)')
            ->addArgument('port', InputArgument::OPTIONAL, 'Port for listening (default: 3000)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $serverName = $input->getArgument('host') ?: "localhost";
        $port = $input->getArgument('port') ?: 3000;

        $root = $this->application['spark.root'];

        $server = new DevelopmentServer(
            "$root/public",
            "$root/public/index.php"
        );

        $server->run($serverName, $port);
    }
}
