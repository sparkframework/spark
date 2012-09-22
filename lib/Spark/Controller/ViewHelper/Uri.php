<?php

namespace Spark\Controller\ViewHelper;

trait Uri
{
    function urlFor($route, $options = [])
    {
        $absolute = @$options['absolute'] ?: false;
        $params   = @$options['params'] ?: [];

        # If the URL is an array, then treat it as params array and
        # use the default route. This way you can do $this->redirect(['controller' => 'index']);`
        if (!$options and is_array($route)) {
            $params = $route;

            if (!isset($params['controller'])) {
                $params['controller'] = $this->application['request']->attributes->get('controller');
            }

            $route = "default";
        }

        return $this->application['url_generator']->generate($route, $params, $absolute);
    }
}
