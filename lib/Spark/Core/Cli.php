<?php

namespace Spark\Core;

use Symfony\Component\Console;

class Cli
{
    protected $console;
    protected $application;

    function __construct()
    {
        if ($bootstrap = $this->findApplicationBootstrap()) {
            if (!isset($_SERVER['SPARK_ENV'])) $_SERVER['SPARK_ENV'] = 'development';

            $this->application = require($bootstrap);
        } else {
            $this->application = new \Spark\Application;
        }

        $this->console = $this->application['console'];
    }

    function run()
    {
        $this->console->run();
    }

    protected function findApplicationBootstrap()
    {
        $path = "config/bootstrap.php";
        $cwd = getcwd();

        while (!$rp = realpath("$cwd/$path")) {
            $cwd .= '/..';

            if (realpath($cwd) === false) {
                break;
            }
        }

        return $rp;
    }
}
