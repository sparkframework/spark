<?php

namespace Spark\Core;

use Symfony\Component\ClassLoader\UniversalClassLoader;
use Symfony\Component\Console;

use Silex\Application;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\MonologServiceProvider;
use Silex\Provider\TwigServiceProvider;

use Pipe\Silex\PipeServiceProvider;
use Spark\ActionPack\ActionPackServiceProvider;
use Spark\ActionPack\View;
use Kue\LocalQueue;
use CHH\Silex\CacheServiceProvider;
use Spark\Support\Strings;

class CoreServiceProvider implements \Silex\ServiceProviderInterface
{
    function register(Application $app)
    {
        # Override Silex' controllers factory with our own builder, which features
        # more convenient route building methods.
        $app["controllers_factory"] = function($app) {
           return new \Spark\ActionPack\ControllerCollection($app["route_factory"]);
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

        $app['spark.action_pack.view_path'] = $app->share(function($app) {
            return [
                 "{$app['spark.root']}/app/views",
                 "{$app['spark.root']}/app/views/layouts"
            ];
        });

        $app['spark.default_module'] = function($app) {
            return $app['spark.app.name'];
        };

        $app['spark.generators'] = $app->share(function($app) {
            $generators = new Command\Generate($app);
            $generators->register('controller', new Generator\ControllerGenerator);

            return $generators;
        });

        $this->setupConsole($app);

        $app['queue'] = $app->share(function($app) {
            return new LocalQueue;
        });

        $this->setupCacheServiceProvider($app);

        $app->register(new MonologServiceProvider, array(
            'monolog.logfile' => function() use ($app) {
                return $app['spark.data_directory'] . '/app.log';
            }
        ));

        if (php_sapi_name() == "cli-server") {
            $app['monolog'] = $app->share($app->extend('monolog', function($logger) {
                $logger->pushHandler(new \Monolog\Handler\StreamHandler('php://stderr'));

                return $logger;
            }));
        }

        $app['stack'] = $app->share(function() {
            return new \Stack\Builder;
        });

        $app->register(new SessionServiceProvider);
        $app->register(new UrlGeneratorServiceProvider);

        $app->register(new PipeServiceProvider, [
            'pipe.root' => $app->share(function($app) { return "{$app['spark.root']}/app/assets"; })
        ]);

        $app->register(new ActionPackServiceProvider);
        $this->setupActionPackServiceProvider($app);
    }

    protected function setupConsole($app)
    {
        $app['console'] = $app->share(function($app) {
            $console = new Console\Application('spark', \Spark\Spark::VERSION);

            $console->add(new Command\CreateApplication);
            $console->add($app['spark.generators']);
            $console->add(new Command\Server($app));
            $console->add(new Command\Console($app));
            $console->add(new Command\AppInfo($app));

            $queueWorker = new Command\QueueWorker($app['queue']);
            $queueWorker->setSilexApplication($app);

            $console->add($queueWorker);
            $console->add(new Command\Upgrade);

            return $console;
        });
    }

    protected function setupActionPackServiceProvider($app)
    {
        $app['spark.action_pack.controllers'] = $app->share(
            $app->extend('spark.action_pack.controllers', function($controllers) use ($app) {
                $controllers->setDefaultModule($app['spark.default_module']);
                $controllers->registerModule($app['spark.default_module'], Strings::camelize($app['spark.app.name']));

                return $controllers;
            })
        );

        $app->register(new TwigServiceProvider, array(
            'twig.paths' => function() use ($app) {
                return $app['spark.action_pack.render_pipeline']->scriptPath->paths();
            },
            'twig.options' => function() use ($app) {
                return ['cache' => "{$app['spark.data_directory']}/twig_cache"];
            }
        ));

        $app['twig'] = $app->share($app->extend('twig', function($twig) use ($app) {
            foreach ($app['spark.action_pack.view.helpers'] as $id => $helper) {
                $twig->addGlobal($id, $helper);
            }

            return $twig;
        }));

        $app['spark.action_pack.view.script_path'] = $app->share(
            $app->extend('spark.action_pack.view.script_path', function($scriptPath) {
                $scriptPath->appendExtensions(\MetaTemplate\Template::getEngines()->getEngineExtensions());
                $scriptPath->appendExtensions(['.phtml', '.php.html']);
                return $scriptPath;
            })
        );

        $app['dispatcher'] = $app->share(
            $app->extend('dispatcher', function($dispatcher) use ($app) {
                $dispatcher->addSubscriber(new View\TwigStrategy($app['twig']));

                $dispatcher->addSubscriber(new View\MetaTemplateStrategy(
                    $app['spark.action_pack.view.script_path'],
                    $app['spark.action_pack.view.helpers'])
                );

                return $dispatcher;
            })
        );
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
