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

use Kue\Worker;
use Kue\Queue;

class QueueWorker extends Command
{
    protected $silexApplication;

    function __construct(Queue $queue)
    {
        parent::__construct();

        $this->log = new Logger('spark/queue:worker');
        $this->log->pushHandler(new StreamHandler(STDERR));
    }

    function setSilexApplication(Application $app)
    {
        $this->silexApplication = $app;
    }

    protected function configure()
    {
        parent::configure();

        $this->setName('queue:worker');
    }

    protected function setupWorker(Worker $worker)
    {
        parent::setupWorker($worker);

        $app = $this->silexApplication;

        $worker->on('init', function($job) use ($app) {
            if ($job instanceof ApplicationAware) {
                $job->setApplication($app);
            }
        });
    }
}

