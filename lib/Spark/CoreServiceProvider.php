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

        $app['spark.default_module'] = function($app) {
            return $app['spark.app.name'];
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

        $app['spark.controller_class_resolver'] = $app->share(function($app) {
            return new Controller\EventListener\ControllerClassResolver($app, $app["spark.controller_directory"]);
        });

        $app['spark.render'] = $app->share(function($app) {
            $render = new Controller\RenderPipeline;

            $render->addFormat(function($viewContext) {
                return $viewContext->options['text'];
            }, 'text/plain');

            $render->addFormat(function($viewContext) {
                if (isset($viewContext->options['html'])) {
                    return $viewContext->options['html'];
                }
            }, 'text/html');

            $render->addFormat(function($viewContext) use ($app) {
                if (!isset($viewContext->options['script'])) return;

                $script = $viewContext->options['script'];

                if (!pathinfo($script, PATHINFO_EXTENSION)) {
                    $script .= '.twig';
                }

                return $app['twig']->render($script, (array) $viewContext->context);
            }, 'text/html');

            $render->addFormat(function($viewContext) {
                $flags = 0;

                if (@$options['pretty']) {
                    $flags |= JSON_PRETTY_PRINT;
                }

                return json_encode($viewContext->options['json'], $flags);
            }, 'application/json');

            return $render;
        });

        $app["dispatcher"] = $app->extend("dispatcher", function($dispatcher, $app) {
            $dispatcher->addSubscriber($app['spark.controller_class_resolver']);

            $dispatcher->addSubscriber(new Controller\EventListener\AutoViewRender(
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

        $app->register(new PipeServiceProvider, [
            'pipe.root' => function($app) { return "{$app['spark.root']}/app/assets"; }
        ]);

        $app->register(new SessionServiceProvider);
        $app->register(new UrlGeneratorServiceProvider);
    }

    function boot(SilexApplication $app)
    {
        $app['spark.class_loader']->register();
    }
}
