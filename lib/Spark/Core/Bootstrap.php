<?php

namespace Spark\Core;

use Spark\Application;

class Bootstrap
{
    static $paths = [
        'config/initializers' => 'config/intializers',
        'config/routes.php' => 'config/routes.php',
        'config/application.php' => 'config/application.php',
        'config/environments' => 'config/environments'
    ];

    # Runs before all configuration is done.
    static function start(Application $app){}

    # Runs after all configuration is done, before run() is called.
    static function end(Application $app){}

    static function bootstrap($root, $environment)
    {
        require __DIR__ . '/../version_check.php';

        \Symfony\Component\HttpFoundation\Request::enableHttpMethodParameterOverride();

        $app = new Application;
        $app['spark.env'] = $environment;
        $app['spark.root'] = $root;

        static::start($app);

        # Run all initializers in 'config/initializers'
        static::initialize($app);

        $configFiles = [
            "$root/" . static::$paths['config/application.php'],
            "$root/" . static::$paths['config/routes.php'],
            "$root/" . static::$paths['config/environments'] . "/$environment.php"
        ];

        foreach ($configFiles as $file) {
            if (is_file($file)) {
                # Ensure a fresh scope with only the '$app' present
                # for each config file
                $c = function($app, $_file) {
                    require_once($_file);
                };

                $c($app, $file);
            }
        }

        static::end($app);

        return $app;
    }

    private static function initialize(Application $app)
    {
        $initializers = "{$app['spark.root']}/" . static::$paths['config/initializers'];

        if (!is_dir($initializers)) {
            return;
        }

        foreach (new \FilesystemIterator($initializers) as $f) {
            # Ensure a fresh scope for each initializer
            $initializer = function($app, $_file) {
                require_once($_file->getRealpath());
            };

            $initializer($app, $f);
        }
    }
}

