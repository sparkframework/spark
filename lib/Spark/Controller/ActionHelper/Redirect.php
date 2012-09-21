<?php

namespace Spark\Controller\ActionHelper;

trait Redirect
{
    function redirect($url, $options = [])
    {
        $absolute = @$options['absolute'] ?: false;
        $params   = @$options['params'] ?: [];
        $code     = @$options['code'] ?: 302;

        # If the URL is an array, then treat it as params array and
        # use the default route. This way you can do $this->redirect(['controller' => 'index']);`
        if (!$options and is_array($url)) {
            $params = $url;

            if (!isset($params['controller'])) {
                $params['controller'] = $this->request()->attributes->get('controller');
            }

            $url = "default";
        }

        # Use the UrlGenerator to construct the URL if a route name was passed
        if ($route = $this->application['routes']->get($url)) {
            $url = $this->application['url_generator']->generate($url, $params, $absolute);
        }

        return $this->application->redirect($url, $code);
    }
}
