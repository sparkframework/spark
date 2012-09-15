<?php

namespace Spark\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Response;

abstract class Base
{
    use ActionHelper\Redirect;

    protected $application;
    protected $response;
    protected $flash;

    private $filters = [];

    function __construct()
    {
        $this->response = new Response;
        $this->setup();
    }

    function setup()
    {}

    function beforeFilter($filter, $options = [])
    {
        return $this->addFilter("before", $filter, $options);
    }

    function afterFilter($filter, $options = [])
    {
        return $this->addFilter("after", $filter, $options);
    }

    function render($options = [])
    {
        $attributes = $this->request()->attributes;

        if (!$options) {
            $options['script'] = $attributes->get('controller') . '/' . $attributes->get('action');
        }

        if (isset($options['status'])) {
            $this->response->setStatusCode($options['status']);
        }

        $options['context'] = $this;

        $this->application['spark.render']->render($this->response, $options);
        return $this->response;
    }

    function request()
    {
        return $this->application['request'];
    }

    function response()
    {
        return $this->response;
    }

    function flash()
    {
        return $this->flash ?: $this->flash = $this->application['session']->getFlashBag();
    }

    function session()
    {
        return $this->application['session'];
    }

    function application()
    {
        return $this->application;
    }

    function setApplication(Application $application)
    {
        $this->application = $application;
    }

    function onBeforeFilter()
    {
        return $this->dispatchFilters('before');
    }

    function onAfterFilter()
    {
        return $this->dispatchFilters('after');
    }

    private function dispatchFilters($type)
    {
        if (!isset($this->filters[$type])) return;

        foreach ($this->filters[$type] as $filter) {
            list($callback, $options) = $filter;

            $returnValue = $callback($this);

            if ($returnValue instanceof Response) return $returnValue;
        }
    }

    private function addFilter($type, $filter, $options)
    {
        if (!isset($this->filters[$type])) {
            $this->filters[$type] = [];
        }

        if (is_string($filter) and is_callable([$this, $filter])) {
            $callback = [$this, $filter];
        } else if (is_callable($filter)) {
            $callback = $filter;
        }

        $this->filters[$type][] = [$callback, $options];

        return $this;
    }
}
