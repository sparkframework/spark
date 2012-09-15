<?php

namespace Spark\Controller;

use Symfony\Component\HttpFoundation\Response;

class RenderPipeline
{
    protected $handlers = [];

    function addFormat($format, callable $handler)
    {
        if (!isset($this->handlers[$format])) {
            $this->handlers[$format] = [];
        }

        $this->handlers[$format][] = $handler;
        return $this;
    }

    function render(Response $response, $options = [])
    {
        $format = key(array_intersect_key($this->handlers, $options)) ?: "html";

        if (!isset($this->handlers[$format])) {
            throw new \UnexpectedValueException("Unknown format '$format'");
        }

        foreach ($this->handlers[$format] as $handler) {
            $returnValue = $handler($response, $options);

            if ($returnValue instanceof Response) {
                return $response;
            }
        }
    }
}
