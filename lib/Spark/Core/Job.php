<?php

namespace Spark\Core;

use Kue\Job as JobInterface;
use Silex\Application;

abstract class Job implements JobInterface, ApplicationAware
{
    protected $application;

    function setApplication(Application $app)
    {
        $this->application = $app;
    }
}

