<?php

namespace Spark\Controller;

use Silex\Application;
use Silex\Provider\TwigServiceProvider;

class ControllerServiceProvider implements \Silex\ServiceProviderInterface
{
    function register(Application $app)
    {
        $app->register(new TwigServiceProvider, [
            'twig.path' => function($app) {
                return $app['spark.view_directory'];
            }
        ]);

        $app['spark.controller_directory'] = function($app) {
            return "{$app['spark.root']}/app/controllers";
        };

        $app['spark.view_directory'] = function($app) {
            return "{$app['spark.root']}/app/views";
        };

        $app['spark.default_module'] = function($app) {
            return $app['spark.app.name'];
        };

        $app['spark.controller_class_resolver'] = $app->share(function($app) {
            return new EventListener\ControllerClassResolver($app, $app["spark.controller_directory"]);
        });

        $app['spark.render'] = $app->share(function($app) {
            $render = new RenderPipeline;

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

            $dispatcher->addSubscriber(new EventListener\AutoViewRender(
                $app['spark.render'], $app['spark.controller_class_resolver']
            ));

            return $dispatcher;
        });
    }

    function boot(Application $app)
    {
    }
}
