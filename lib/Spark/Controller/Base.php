<?php

namespace Spark\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Response;
use Spark\Core\ApplicationAware;

abstract class Base implements ApplicationAware
{
    use Traits\Redirect;
    use Traits\Filters;

    protected $application;
    protected $response;
    protected $flash;

    function __construct()
    {
        $this->response = new Response;
        $this->setup();
    }

    function setup()
    {}

    function render($options = [])
    {
        $attributes = $this->request()->attributes;

        if (!$options) {
            $options['script'] = $attributes->get('controller') . '/' . $attributes->get('action');
        }

        if (isset($options['status'])) {
            $this->response()->setStatusCode($options['status']);
            unset($options['status']);
        }

        return $this->application['spark.render']->render($options, $this->response());
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
}
