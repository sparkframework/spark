<?php

namespace Spark\Controller;

use Symfony\Component\HttpFoundation\Response;

class RenderPipeline
{
    protected $renderers = [];

    protected $formats = [
        'json' => 'application/json',
        'html' => 'text/html',
        'text' => 'text/plain',
        'xml' => 'application/xml'
    ];

    function addFormat(callable $renderer, $contentType = null)
    {
        if (is_object($renderer)) {
            $class = get_class($renderer);

            if (is_callable([$class, "getContentType"])) {
                $contentType = $class::getContentType();
            }
        }

        if (null === $contentType) {
            throw new \InvalidArgumentException("No Content Type given");
        }

        if (!isset($this->renderers[$contentType])) {
            $this->renderers[$contentType] = [];
        }

        $this->renderers[$contentType][] = $renderer;
        return $this;
    }

    function invokeHandlers(ViewContext $context)
    {
        $format = $context->format;
        $contentType = $this->formats[$format];

        if (!isset($this->renderers[$contentType])) {
            throw new \UnexpectedValueException("No Renderer registered for '$contentType'");
        }

        foreach ($this->renderers[$contentType] as $renderer) {
            $returnValue = $renderer($context);

            if (null !== $returnValue) {
                return $returnValue;
            }
        }
    }

    function render($options = [], Response $response = null)
    {
        $format = key(array_intersect_key($this->formats, $options)) ?: "html";

        $response = $response ?: new Response;

        $viewContext = new ViewContext($this);
        $viewContext->format = $format;
        $viewContext->context = @$options['context'];
        $viewContext->options = $options;

        $response->setContent($viewContext->render());

        $contentType = $this->formats[$format];
        $response->headers->set('Content-Type', $contentType);

        return $response;
    }
}
