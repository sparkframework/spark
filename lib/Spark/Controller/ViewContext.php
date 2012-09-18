<?php

namespace Spark\Controller;

use Symfony\Component\HttpFoundation\Response;

class ViewContext
{
    public $format = "html";
    public $context;
    public $options = [];

    protected $renderPipeline;

    function __construct(RenderPipeline $renderPipeline)
    {
        $this->renderPipeline = $renderPipeline;
    }

    function render()
    {
        return $this->renderPipeline->invokeHandlers($this);
    }

    function __toString()
    {
        try {
            return $this->render();
        } catch (\Exception $e) {
            # Ignore
        }
    }
}
