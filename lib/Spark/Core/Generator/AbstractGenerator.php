<?php

namespace Spark\Core\Generator;

use Silex\Application;
use Symfony\Component\Console\Output\OutputInterface;

abstract class AbstractGenerator implements GeneratorInterface
{
    protected $application;
    protected $out;

    function setOutput(OutputInterface $output)
    {
        $this->out = $output;
    }

    function setApplication(Application $application)
    {
        $this->application = $application;
    }
}
