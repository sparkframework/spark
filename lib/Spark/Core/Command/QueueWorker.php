<?php

namespace Spark\Core\Command;

use Silex\Application;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class QueueWorker extends Command
{
    function __construct(Application $app)
    {
        $this->application = $app;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('queue:worker')
            ->setDescription('Runs a single queue worker')
            ->addOption('require', 'r', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'File(s) to require before accepting jobs')
            ->addArgument('socket', InputArgument::OPTIONAL, 'Socket for the server to listen on (default: tcp://0.0.0.0:9999');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $require = $input->getOption('require');

        foreach ($require as $file) {
            require($file);
        }

        $this->application['spark.class_loader']->register();

        if (isset($this->application['queue.socket'])) {
            $socket = $this->application['queue.socket'];
        } else {
            $socket = $input->getArgument('socket') ?: "tcp://0.0.0.0:9999";
        }

        $server = stream_socket_server($socket, $errno, $errstr);

        if (false === $server) {
            throw new \InvalidArgumentException(sprintf(
                'Could not start server: %s', $errstr
            ));
        }

        $output->writeln(sprintf('Listening for jobs on <info>%s</info>', $socket));
        $output->writeln('Stop with [CTRL]+[c]');

        for (;;) {
            $r = [$server];
            $w = null;
            $x = null;

            if (stream_select($r, $w, $x, 0, 500000) > 0 and $r) {
                $conn = stream_socket_accept($r[0]);

                while ($jobData = fgets($conn)) {
                    $job = unserialize($jobData);
                    $job->run();
                }
            }
        }
    }
}

