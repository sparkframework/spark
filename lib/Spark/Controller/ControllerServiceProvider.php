<?php

namespace Spark\Controller;

use Silex\Application;
use Silex\Provider\TwigServiceProvider;

class ControllerServiceProvider implements \Silex\ServiceProviderInterface
{
    function register(Application $app)
    {
        $app["controllers_factory"] = function($app) {
            return new ControllerCollection($app["route_factory"]);
        };

        $app['spark.controller_directory'] = function($app) {
            return "{$app['spark.root']}/app/controllers";
        };

        $app['spark.view_path'] = function($app) {
            return [
                 "{$app['spark.root']}/app/views",
                 "{$app['spark.root']}/app/views/layouts"
            ];
        };

        $app['spark.view_context'] = $app->share(function($app) {
            $class = $app['spark.view_context_class'];
            return new $class($app);
        });

        $app['spark.view_context_class'] = function($app) {
            return "\\{$app['spark.app.name']}\\ViewContext";
        };

        $app['spark.default_module'] = function($app) {
            return $app['spark.app.name'];
        };

        $app['spark.controller_class_resolver'] = $app->share(function($app) {
            return new EventListener\ControllerClassResolver($app, $app["spark.controller_directory"]);
        });

        $app['spark.render_pipeline'] = $app->share(function($app) {
            $render = new RenderPipeline($app['spark.view_context'], $app['spark.view_path']);

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
        });

        $app["dispatcher"] = $app->extend("dispatcher", function($dispatcher, $app) {
            $dispatcher->addSubscriber($app['spark.controller_class_resolver']);

            $dispatcher->addSubscriber(new EventListener\AutoViewRender(
                $app['spark.render_pipeline'], $app['spark.controller_class_resolver']
            ));

            return $dispatcher;
        });
    }

    function boot(Application $app)
    {
        $app->error(function(\Exception $e, $code) use ($app) {
            $renderPipeline = $app['spark.render_pipeline'];

            if ($script = $renderPipeline->scriptPath->find("error/$code")) {
                $context = (object) [
                    'exception' => $e,
                    'code' => $code
                ];

                return $renderPipeline->render(['script' => "error/$code", 'context' => $context]);
            }
        });
    }
}
