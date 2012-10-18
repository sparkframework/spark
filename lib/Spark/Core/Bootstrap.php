<?php

namespace Spark\Core;

use Spark\Application;

class Bootstrap
{
    static function bootstrap($root, $environment)
    {
        $app = new Application;
        $app['spark.env'] = $environment;
        $app['spark.root'] = $root;
        $app['spark.data_dir'] = "$root/data";

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

        return $app;
    }
}
