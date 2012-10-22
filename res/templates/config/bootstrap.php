<?php

namespace __AppName__;

use Spark\Application;

class Bootstrap extends \Spark\Core\Bootstrap
{
    static function start(Application $app)
    {
        # Include your code here, which does things before
        # configuration files are included.
    }

    static function end(Application $app)
    {
        # Include your code here, which runs after all configuration has
        # happend.
    }
}

return Bootstrap::bootstrap(__DIR__ . '/../', @$_SERVER['SPARK_ENV'] ?: "development");

