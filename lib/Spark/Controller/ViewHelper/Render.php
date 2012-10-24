<?php

namespace Spark\Controller\ViewHelper;

trait Render
{
    function render($script, $options = [])
    {
        $context = clone $this;

        if (strpos($script, '/') !== false) {
            $partial = basename($script);
            $context->script = dirname($script) . "_$partial";
        } else {
            $controllerName = $this->application['request']->attributes->get('controller');
            $context->script = "$controllerName/_$script";
        }

        $context->parent = null;

        $render = $this->application['spark.render_pipeline'];

        if ($collection = @$options['collection']) {
            $returnValue = '';

            foreach ($collection as $entry) {
                $context->context = $entry;
                $returnValue .= $render->renderContext($context);
            }

            return $returnValue;
        }

        return $render->renderContext($context);
    }
}
