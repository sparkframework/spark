<?php

$app['controllers']->draw(function($routes) {

    # This is the default route. It tries to find controller and action
    # by looking at the request URI.
    $routes->match('/{controller}/{action}/{id}', null)
        ->value('controller', 'index')
        ->value('action', 'index')
        ->value('id', null)
        ->bind('default');
});

