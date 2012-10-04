<?php

namespace Spark\Core;

use Symfony\Component\ClassLoader\UniversalClassLoader;
use Symfony\Component\Console;

use Silex\Application;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;

use Pipe\Silex\PipeServiceProvider;
use Spark\Controller\ControllerServiceProvider;

class CoreServiceProvider implements \Silex\ServiceProviderInterface
{
    function register(Application $app)
    {
        $app['config'] = $app->share(function($app) {
            return new ConfigBuilder;
        });

        $app["controllers_factory"] = function($app) {
            return new \Spark\Controller\ControllerCollection($app["route_factory"]);
        };

        $app["spark.class_loader"] = $app->share(function($app) {
            $loader = new UniversalClassLoader;

            $loader->registerPrefixFallbacks([
                "{$app['spark.root']}/lib",
            ]);

            $loader->registerNamespaceFallbacks([
                $app['spark.controller_directory'],
                "{$app['spark.root']}/lib"
            ]);

            return $loader;
        });

        $app['console'] = $app->share(function($app) {
            $console = new Console\Application;

            @$console->add(new Command\CreateApplication);
            @$console->add(new Command\GenerateController($app));
            @$console->add(new Command\Server($app));

            return $console;
        });

        $app->register(new PipeServiceProvider, [
            'pipe.root' => function($app) { return "{$app['spark.root']}/app/assets"; }
        ]);

        $app->register(new SessionServiceProvider);
        $app->register(new UrlGeneratorServiceProvider);
        $app->register(new ControllerServiceProvider);
    }

    function boot(Application $app)
    {
        $app['config']->flush($app);
        $app['spark.class_loader']->register();
    }
}
