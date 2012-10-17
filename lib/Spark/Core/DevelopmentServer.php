<?php

namespace Spark\Core;

use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\ProcessBuilder;

class DevelopmentServer
{
    protected $documentRoot;
    protected $router;

    function __construct($documentRoot, $router)
    {
        $this->documentRoot = $documentRoot;
        $this->router = $router;
    }

    function run($serverName = null, $port = null)
    {
        $serverName = $serverName ?: "localhost";
        $port = $port ?: 3000;

        $builder = new ProcessBuilder();
        $builder->add((new PhpExecutableFinder)->find());

        # Start PHP in development server mode
        $builder->add('-S');
        $builder->add("$serverName:$port");

        # Add the document root
        $builder->add('-t');
        $builder->add($this->documentRoot);

        # Add the router script
        $builder->add($this->router);

        $process = $builder->getProcess();
        $process->setTimeout(null);

        printf("Running development server on %s:%d ..." . PHP_EOL, $serverName, $port);
        printf("Stop by pressing [CTRL] + [c]\n");

        $process->run(function($type, $err) {
            fwrite(STDERR, "$err\n");
        });
    }
}
