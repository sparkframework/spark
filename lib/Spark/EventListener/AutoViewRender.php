<?php

namespace Spark\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpFoundation\Response;

use Spark\Controller\RenderPipeline;

class AutoViewRender implements EventSubscriberInterface
{
    protected $renderPipeline;
    protected $resolver;

    static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => 'renderView'
        ];
    }

    function __construct(RenderPipeline $render, ControllerClassResolver $resolver)
    {
        $this->renderPipeline = $render;
        $this->resolver = $resolver;
    }

    function renderView(GetResponseForControllerResultEvent $event)
    {
        $request = $event->getRequest();
        $attributes = $request->attributes;

        if ($attributes->get('spark.disable_autorender', false)) {
            return;
        }

        if (!$request->attributes->has('controller') and !$request->attributes->has('action')) {
            return;
        }

        $controllerName = $request->attributes->get('controller');
        $actionName = $request->attributes->get('action');

        $controller = $this->resolver->getController($controllerName);
        $response = $controller->response();

        $this->renderPipeline->render($response, [
            'script' => "$controllerName/$actionName", 'context' => $controller
        ]);

        $event->setResponse($response);
    }
}
