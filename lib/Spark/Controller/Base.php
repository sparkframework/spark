<?php

namespace Spark\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Response;
use Spark\Core\ApplicationAware;

abstract class Base implements ApplicationAware
{
    use ActionHelper\Filters;
    use ActionHelper\Redirect;
    use ActionHelper\Layout;

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

        if (isset($options['response'])) {
            $response = $options['response'];
            unset($options['response']);
        } else {
            $response = $this->response();
        }

        return $this->application['spark.render_pipeline']->render($options, $response);
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
