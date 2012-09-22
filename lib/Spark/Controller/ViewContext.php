<?php

namespace Spark\Controller;

use Symfony\Component\HttpFoundation\Response;

class ViewContext
{
    use ViewHelper\Assets;
    use ViewHelper\Flash;
    use ViewHelper\Uri;

    public $format = "html";
    public $context;
    public $options = [];
    public $script;
    public $parent;
    public $response;

    protected $blocks = [];
    protected $application;

    function __construct(\Spark\Application $app)
    {
        $this->application = $app;
        $this->context = (object) [];
    }

    function setBlock($name, $content)
    {
        $this->blocks[$name] = $content;
        return $this;
    }

    function block($name)
    {
        if (isset($this->blocks[$name])) {
            return (string) $this->blocks[$name];
        }
    }

    function blocks()
    {
        return array_map('strval', $this->blocks);
    }

    function __get($property)
    {
        if (isset($this->context->$property)) {
            if (is_callable($this->context->$property)) {
                $callback = $this->context->$property;
                return $callback($this);
            }

            return $this->context->$property;
        }
    }

    function __call($method, $argv)
    {
        if (isset($this->$method)) {
            return call_user_func_array($this->$method, $argv);
        }

        throw new \BadMethodCallException(sprintf(
            'Call to undefined method %s::%s()', get_called_class(), $method
        ));
    }

    function __toString()
    {
        try {
            return $this->render();
        } catch (\Exception $e) {
            # Ignore
            # TODO: Log exception
        }
    }
}
