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

    function __construct()
    {
        $this->response = new Response;
    }

    function render($options = null)
    {
        if (null === $options) {
            $options['script'] = $this->request()->attributes->get('controller')
                    . '/' . $this->request()->attributes->get('action');
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

    function application()
    {
        return $this->application;
    }

    function setApplication(Application $application)
    {
        $this->application = $application;
    }
}
