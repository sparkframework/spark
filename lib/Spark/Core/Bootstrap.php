<?php

namespace Spark\Core;

use Spark\Application;

class Bootstrap
{
    # Runs before all configuration is done.
    static function start(Application $app){}

    # Runs after all configuration is done, before run() is called.
    static function end(Application $app){}

    static function bootstrap($root, $environment)
    {
        $app = new Application;
        $app['spark.env'] = $environment;
        $app['spark.root'] = $root;

        static::start($app);

        $configFiles = [
            "$root/config/application.php",
            "$root/config/routes.php",
            "$root/config/environments/$environment.php"
        ];

        foreach ($configFiles as $config) {
            if (is_file($config)) {
                require_once($config);
            }
        }

        static::end($app);

        return $app;
    }
}
