<?php

namespace Spark\Core;

class WebTestCase extends \Silex\WebTestCase
{
    static protected $bootstrap;

    static function setBootstrap($path)
    {
        static::$bootstrap = $path;
    }

    function createApplication()
    {
        $app = require(static::$bootstrap);

        $app['debug'] = true;

        # Don't turn exceptions into HTML pages
        unset($app['exception_handler']);

        $app['session.test'] = true;

        return $app;
    }
}

