<?php

$app['stack'] = $app->share($app->extend('stack', function($stack) use ($app) {
    # Add your middleware components here, for example:
    # $stack->push('Stack\Oauth', $app['oauth.config']);

    return $stack;
}));
