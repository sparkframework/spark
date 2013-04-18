<?php

namespace Spark\Core;

class Cli
{
    function run()
    {
        if (!isset($_SERVER['SPARK_ENV'])) $_SERVER['SPARK_ENV'] = 'development';

        if ($bootstrap = $this->findApplicationBootstrap()) {
            $app = require($bootstrap);
        } else {
            $app = new \Spark\Application;
        }

        return $app['console']->run();
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
