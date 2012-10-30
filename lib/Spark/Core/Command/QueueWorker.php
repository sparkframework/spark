<?php

namespace Spark\Core\Command;

use Silex\Application;

use Spark\Core\ApplicationAware;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class QueueWorker extends Command
{
    protected $log;

    function __construct(Application $app)
    {
        $this->application = $app;

        $this->log = new Logger('spark/queue:worker');
        $this->log->pushHandler(new StreamHandler(STDERR));

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('queue:worker')
            ->setDescription('Runs a single queue worker')
            ->addOption('require', 'r', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'File(s) to require before accepting jobs');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $require = $input->getOption('require');

        foreach ($require as $file) {
            require($file);
        }

        $this->application['spark.class_loader']->register();

        $queue = $this->application['queue'];

        $output->writeln(sprintf('Listening for jobs using %s', get_class($queue)));
        $output->writeln('Stop with [CTRL]+[c]');

        for (;;) {
            $job = $queue->pop();

            if ($job) {
                $this->log->addInfo(sprintf('Accepted job %s', get_class($job)), ['job' => $job]);

                if ($job instanceof ApplicationAware) {
                    $job->setApplication($this->application);
                }

                set_error_handler(function($code, $message, $file, $line) use ($job) {
                    $this->log->addError(
                        sprintf('Job %s failed: "%s" in file %s:%d', get_class($job), $message, $file, $line),
                        ['job' => $job]
                    );
                });

                try {
                    $job->run();

                    $this->log->addInfo(sprintf('Job "%s" finished successfully', get_class($job)), ['job' => $job]);
                } catch (\Exception $e) {
                    $this->log->addError(sprintf('Job "%s" failed: %s', get_class($job), $e), ['job' => $job]);
                }

                restore_error_handler();
            }
        }
    }
}

