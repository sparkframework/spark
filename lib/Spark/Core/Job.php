<?php

namespace Spark\Core;

use Spark\Queue\Job as JobInterface;
use Silex\Application;

abstract class Job implements JobInterface, ApplicationAware
{
    protected $application;

    function setApplication(Application $app)
    {
        $this->application = $app;
    }
}
