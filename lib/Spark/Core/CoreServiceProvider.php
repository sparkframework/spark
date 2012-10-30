<?php

namespace Spark\Core;

use Symfony\Component\ClassLoader\UniversalClassLoader;
use Symfony\Component\Console;

use Silex\Application;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\MonologServiceProvider;

use Pipe\Silex\PipeServiceProvider;
use Spark\Controller\ControllerServiceProvider;
use Spark\Queue\LocalQueue;

use CHH\Silex\CacheServiceProvider;

class CoreServiceProvider implements \Silex\ServiceProviderInterface
{
    function register(Application $app)
    {
        # Override Silex' controllers factory with our own builder, which features
        # more advanced route building methods, like 'resource'.
        $app["controllers_factory"] = function($app) {
            return new \Spark\Controller\ControllerCollection($app["route_factory"]);
        };

        $app['config'] = $app->share(function($app) {
            return new ConfigBuilder;
        });

        $app['spark.config_directory'] = $app->share(function($app) {
            return "{$app['spark.root']}/config";
        });

        $app['spark.controller_directory'] = $app->share(function($app) {
            return "{$app['spark.root']}/app/controllers";
        });

        $app['spark.data_directory'] = $app->share(function($app) {
            return "{$app['spark.root']}/data";
        });

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

        $app['spark.view_path'] = $app->share(function($app) {
            return [
                 "{$app['spark.root']}/app/views",
                 "{$app['spark.root']}/app/views/layouts"
            ];
        });

        $app['spark.view_context_class'] = function($app) {
            return "\\{$app['spark.app.name']}\\ViewContext";
        };

        $app['spark.default_module'] = function($app) {
            return $app['spark.app.name'];
        };

        $app['spark.generators'] = $app->share(function($app) {
            $generators = new Command\Generate($app);
            $generators->register('controller', new Generator\ControllerGenerator);

            return $generators;
        });

        $app['console'] = $app->share(function($app) {
            $console = new Console\Application;

            $console->add(new Command\CreateApplication);
            $console->add($app['spark.generators']);
            $console->add(new Command\Server($app));
            $console->add(new Command\QueueWorker($app));
            $console->add(new Command\Upgrade);

            return $console;
        });

        $app['queue'] = $app->share(function($app) {
            return new LocalQueue;
        });

        $this->setupCacheServiceProvider($app);

        $app->register(new MonologServiceProvider, array(
            'monolog.logfile' => function() use ($app) {
                return $app['spark.data_directory'] . '/app.log';
            }
        ));

        $app->register(new SessionServiceProvider);
        $app->register(new UrlGeneratorServiceProvider);

        $app->register(new PipeServiceProvider, [
            'pipe.root' => $app->share(function($app) { return "{$app['spark.root']}/app/assets"; })
        ]);

        $app->register(new ControllerServiceProvider);
    }

    protected function setupCacheServiceProvider($app)
    {
        $app['cache.options'] = $app->share(function() use ($app) {
            $caches = [];
            $driver = null;

            switch (true) {
            case extension_loaded('apc'):
                $driver = "apc";
                break;
            case extension_loaded('xcache'):
                $driver = "xcache";
                break;
            case function_exists('zend_shm_cache_fetch'):
                $driver = "zend_data";
                break;
            }

            if ($driver) {
                $caches['default'] = array('driver' => $driver);
            }

            $caches['file'] = [
                'driver' => 'filesystem',
                'directory' => $app['spark.data_directory']
            ];

            return $caches;
        });

        $app->register(new CacheServiceProvider);
    }

    function boot(Application $app)
    {
        $app['config']->flush($app);
        $app['spark.class_loader']->register();
    }
}
