<?php

namespace Spark\Core;

use Symfony\Component\ClassLoader\UniversalClassLoader;
use Symfony\Component\Console;

use Silex\Application;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\MonologServiceProvider;

use Pipe\Silex\PipeServiceProvider;
use Spark\ActionPack\ActionPackServiceProvider;
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

        $app['spark.action_pack.view_context_class'] = function($app) {
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
            $console = new Console\Application('spark', \Spark\Spark::VERSION);

            $console->add(new Command\CreateApplication);
            $console->add($app['spark.generators']);
            $console->add(new Command\Server($app));

            $queueWorker = new Command\QueueWorker($app['queue']);
            $queueWorker->setSilexApplication($app);

            $console->add($queueWorker);

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

        $app->register(new ActionPackServiceProvider);
        $this->setupActionPackServiceProvider($app);
    }

    protected function setupActionPackServiceProvider($app)
    {
        $app['spark.action_pack.controller_class_resolver'] = $app->share(
            $app->extend('spark.action_pack.controller_class_resolver', function($resolver) use ($app) {
                $resolver->setDefaultModule($app['spark.default_module']);
                $resolver->registerModule($app['spark.default_module'], Strings::camelize($app['spark.app.name']));

                return $resolver;
            })
        );

        $app['spark.action_pack.render_pipeline'] = $app->share(
            $app->extend('spark.action_pack.render_pipeline', function($render) use ($app) {
                $render->addFormat('text/plain', function($viewContext) {
                    $viewContext->parent = null;
                    return $viewContext->options['text'];
                });

                $render->addFormat('text/html', function($viewContext) {
                    if (isset($viewContext->options['html'])) {
                        return $viewContext->options['html'];
                    }
                });

                $render->addFormat('application/json', function($viewContext) {
                    $viewContext->parent = null;
                    $flags = 0;

                    if (@$viewContext->options['pretty']) {
                        $flags |= JSON_PRETTY_PRINT;
                    }

                    return json_encode($viewContext->options['json'], $flags);
                });

                $render->scriptPath->appendExtensions(\MetaTemplate\Template::getEngines()->getEngineExtensions());

                $render->addFallback(function($viewContext) {
                    if (empty($viewContext->script)) return;

                    $template = \MetaTemplate\Template::create($viewContext->script);

                    if ($viewContext->response) {
                        $headers = $viewContext->response->headers;

                        if (is_callable([$template, 'getDefaultContentType']) and !$headers->has('Content-Type')) {
                            $headers->set('Content-Type', $template->getDefaultContentType());
                        }

                        if ($headers->get('Content-Type') !== "text/html") {
                            $viewContext->parent = null;
                        }
                    }

                    return $template->render($viewContext);
                });

                return $render;
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
