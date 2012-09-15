<?php

namespace Spark;

use Symfony\Component\ClassLoader\UniversalClassLoader;
use Silex\Application as SilexApplication;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Pipe\Silex\PipeServiceProvider;

class CoreServiceProvider implements \Silex\ServiceProviderInterface
{
    function register(SilexApplication $app)
    {
        $app['spark.controller_directory'] = function($app) {
            return "{$app['spark.root']}/app/controllers";
        };

        $app['spark.view_directory'] = function($app) {
            return "{$app['spark.root']}/app/views";
        };

        $app["spark.class_loader"] = $app->share(function($app) {
            $loader = new UniversalClassLoader;
            $loader->registerPrefixFallbacks((array) $app['spark.controller_directory']);

            return $loader;
        });

        $app['spark.controller_class_resolver'] = $app->share(function($app) {
            return new EventListener\ControllerClassResolver($app, $app["spark.controller_directory"]);
        });

        $app['spark.render'] = $app->share(function($app) {
            $render = new Controller\RenderPipeline;

            $render->addFormat('text', function($response, $options) {
                $response->headers->set('Content-Type', 'text/plain');
                $response->setContent($options['text']);
            });

            $render->addFormat('html', function($response, $options) use ($app) {
                $script = $options['script'];

                if (!pathinfo($script, PATHINFO_EXTENSION)) {
                    $script .= '.twig';
                }

                $twig = $app['twig'];
                $response->setContent($twig->render($script, (array) $options['context']));
            });

            $render->addFormat('json', function($response, $options) {
                $flags = 0;

                if (@$options['pretty']) {
                    $flags |= JSON_PRETTY_PRINT;
                }

                $response->setContent(json_encode($options['json'], $flags));
                $response->headers->set('Content-Type', 'application/json');
            });

            return $render;
        });

        $app["dispatcher"] = $app->extend("dispatcher", function($dispatcher, $app) {
            $dispatcher->addSubscriber($app['spark.controller_class_resolver']);

            $dispatcher->addSubscriber(new EventListener\AutoViewRender(
                $app['spark.render'], $app['spark.controller_class_resolver']
            ));

            return $dispatcher;
        });

        $app["controllers_factory"] = function($app) {
            return new ControllerCollection($app["route_factory"]);
        };

        $app->register(new TwigServiceProvider, [
            'twig.path' => function($app) {
                return $app['spark.view_directory'];
            }
        ]);

        $app->register(new SessionServiceProvider);
        $app->register(new UrlGeneratorServiceProvider);
    }

    function boot(SilexApplication $app)
    {
        $app->register(new PipeServiceProvider, [
            'pipe.root' => function($app) { return "{$app['spark.root']}/app/assets"; }
        ]);

        $app['spark.class_loader']->register();
    }
}
