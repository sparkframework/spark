<?php

namespace Spark\Controller\ActionHelper;

trait Layout
{
    /**
     * Set layout options
     *
     * @param array $options
     */
    function layout($options = [])
    {
        $renderPipeline = $this->application['spark.render_pipeline'];

        if ($options === false) {
            $renderPipeline->renderLayout = false;
            return;
        }

        if (!is_array($options)) {
            $options = ['script' => (string) $options];
        }

        if (isset($options['script'])) {
            $renderPipeline->layout->script = $options['script'];
        }

        return $this;
    }
}
