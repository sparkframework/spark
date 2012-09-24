<?php

namespace Spark\Controller;

use Symfony\Component\HttpFoundation\Response;
use CHH\FileUtils\PathStack;

class RenderPipeline
{
    public $formats = [
        'json' => 'application/json',
        'html' => 'text/html',
        'text' => 'text/plain',
        'xml' => 'application/xml'
    ];

    public $layout;
    public $renderLayout = true;
    public $scriptPath;

    protected $defaultContext;
    protected $contentTypeHandlers = [];
    protected $fallbackHandlers = [];

    function __construct(ViewContext $defaultContext, $scriptPath = null)
    {
        $this->scriptPath = new PathStack();

        if ($scriptPath !== null) {
            $this->scriptPath->appendPaths($scriptPath);
        }

        $this->scriptPath->appendExtensions(['.phtml', '.html.php']);

        $this->defaultContext = $defaultContext;

        $this->layout = $this->createContext();
        $this->layout->script = "default";
    }

    function addFormat($contentType, callable $handler)
    {
        if (!isset($this->contentTypeHandlers[$contentType])) {
            $this->contentTypeHandlers[$contentType] = [];
        }

        $this->contentTypeHandlers[$contentType][] = $handler;
        return $this;
    }

    function addFallback(callable $handler)
    {
        $this->fallbackHandlers[] = $handler;
        return $this;
    }

    function renderContext(ViewContext $context)
    {
        $format = $context->format;
        $handlers = [];

        if ($context->script) {
            $context->script = $this->scriptPath->find($context->script);
        }

        if ($contentType = @$this->formats[$format] and isset($this->contentTypeHandlers[$contentType])) {
            $handlers = array_merge($handlers, $this->contentTypeHandlers[$contentType]);
        }

        $handlers = array_merge($handlers, $this->fallbackHandlers);

        foreach ($handlers as $handler) {
            $returnValue = $handler($context);

            if (null !== $returnValue) {
                break;
            }
        }

        if ($context->parent) {
            foreach ($context->blocks() as $block => $content) {
                $context->parent->setBlock($block, $content);
            }

            $context->parent->setBlock('content', $returnValue);
            return $this->renderContext($context->parent);
        }

        return $returnValue;
    }

    function render($options = [], Response $response = null)
    {
        $format = key(array_intersect_key($this->formats, $options)) ?: "html";

        $response = $response ?: new Response;

        $viewContext = $this->createContext();
        $viewContext->response = $response;

        if (isset($options['script'])) {
            $viewContext->script = $options['script'];
        }

        $viewContext->format = $format;
        $viewContext->context = @$options['context'];
        $viewContext->options = $options;

        if ($this->renderLayout and @$options['layout'] !== false) {
            $viewContext->parent = clone $this->layout;

            if (!empty($options['layout'])) {
                $viewContext->parent->script = $options['layout'];
            }
        }

        $response->setContent($this->renderContext($viewContext));

        if (!$response->headers->has('Content-Type')) {
            $contentType = $this->formats[$format];
            $response->headers->set('Content-Type', $contentType);
        }

        return $response;
    }

    protected function createContext()
    {
        return clone $this->defaultContext;
    }
}
