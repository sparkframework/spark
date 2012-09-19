<?php

namespace Spark;

use Symfony\Component\ClassLoader\UniversalClassLoader;
use Silex\Application as SilexApplication;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Pipe\Silex\PipeServiceProvider;

class CoreServiceProvider implements \Silex\ServiceProviderInterface
{
    function register(SilexApplication $app)
    {
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

        $app["controllers_factory"] = function($app) {
            return new ControllerCollection($app["route_factory"]);
        };

        $app->register(new PipeServiceProvider, [
            'pipe.root' => function($app) { return "{$app['spark.root']}/app/assets"; }
        ]);

        $app->register(new SessionServiceProvider);
        $app->register(new UrlGeneratorServiceProvider);
        $app->register(new Controller\ControllerServiceProvider);
    }

    function boot(SilexApplication $app)
    {
        $app['spark.class_loader']->register();
    }
}
