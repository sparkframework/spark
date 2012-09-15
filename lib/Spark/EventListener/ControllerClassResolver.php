<?php

namespace Spark\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

use Silex\Application;

class ControllerClassResolver implements EventSubscriberInterface
{
    protected $controllerDirectory;
    protected $controllers = [];
    protected $application;

    static function getSubscribedEvents()
    {
        return [
            KernelEvents::REQUEST => [
                # Register the listener after the kernel's RouterListener (32)
                ["onKernelRequest", 31]
            ]
        ];
    }

    function __construct(Application $app, $controllerDirectory)
    {
        $this->application = $app;
        $this->controllerDirectory = $controllerDirectory;
    }

    function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        $current = $request->attributes->get('_controller');

        # If controller is already callable, then we don't need to do anything
        if (is_callable($current)) {
            return;
        }

        if ((!is_string($current) and false === strpos($current, '#')) and !$request->attributes->has('controller')) {
            return;
        }

        if (false !== strpos($current, '#')) {
            list($controllerName, $actionName) = explode('#', $current);

        } elseif ($request->attributes->has('controller')) {
            $controllerName = $request->attributes->get('controller');
            $actionName = $request->attributes->get('action');
        }

        $route = $this->application['routes']->get($request->attributes->get('_route'));
        $action = $this->camelize($actionName) . "Action";

        $controller = $this->getController($controllerName);

        if (is_callable([$controller, "beforeFilter"])) {
            $route->before([$controller, "beforeFilter"]);
        }

        if (is_callable([$controller, "afterFilter"])) {
            $route->after([$controller, "afterFilter"]);
        }

        $request->attributes->set('action', $actionName);
        $request->attributes->set('controller', $controllerName);

        if (is_callable([$controller, $action])) {
            $request->attributes->set('_controller', [$controller, $action]);
        } else {
            $request->attributes->set('_controller', null);
        }
    }

    protected function camelize($string)
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));
    }

    function getController($name)
    {
        if (class_exists($name)) {
            $class = $name;
        } else {
            $class = $this->camelize($name) . "Controller";
        }

        if (isset($this->controllers[$class])) {
            $controller = $this->controllers[$class];
        } else {
            $controller = new $class;

            if (is_callable([$controller, "setApplication"])) {
                $controller->setApplication($this->application);
            }

            $this->controllers[$class] = $controller;
        }

        return $controller;
    }
}
