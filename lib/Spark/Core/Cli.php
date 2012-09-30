<?php

namespace Spark\Core;

use Symfony\Component\Console;

class Cli
{
    protected $console;
    protected $application;

    function __construct()
    {
        $this->console = new Console\Application;
        $this->console->add(new Command\CreateApplication);

        if ($bootstrap = $this->findApplicationBootstrap()) {
            $this->application = require($bootstrap);

            $this->console->add(new Command\Generate($this->application));
        }
    }

    function run()
    {
        $this->application->run();
    }

    protected function findApplicationBootstrap()
    {
        $path = "config/bootstrap.php";

        while (!realpath("../$path")) {
            $path = "../$path";
        }

        return $path;
    }
}
