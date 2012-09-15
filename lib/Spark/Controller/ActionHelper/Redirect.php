<?php

namespace Spark\Controller\ActionHelper;

trait Redirect
{
    function redirect($url, $options = [])
    {
        $absolute = @$options['absolute'] ?: false;
        $params   = @$options['params'] ?: [];
        $code     = @$options['code'] ?: 302;

        # Use the UrlGenerator to construct the URL if a route name was passed
        if ($route = $this->application['routes']->get($url)) {
            $url = $this->application['url_generator']->generate($url, $params, $absolute);
        }

        return $this->application->redirect($url, $code);
    }
}
