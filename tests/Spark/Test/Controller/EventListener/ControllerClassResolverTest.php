<?php

namespace Spark\Test\Controller\EventListener;

use Spark\Controller\EventListener\ControllerClassResolver;
use Symfony\Component\HttpFoundation\Response;
use Silex\Application;

class HelloController
{
    function indexAction()
    {
        return new Response("Hello World");
    }
}

class ControllerClassResolverTest extends \Silex\WebTestCase
{
    protected $resolver;

    function createApplication()
    {
        $app = new Application;

        $resolver = $this->resolver = new ControllerClassResolver($app);           
        $resolver->registerModule('default', '\\Spark\\Test\\Controller\\EventListener');

        $app['debug'] = true;
        unset($app['exception_handler']);

        $app->extend('dispatcher', function($dispatcher) use ($resolver) {
            $dispatcher->addSubscriber($resolver);

            return $dispatcher;
        });

        return $app;
    }

    function testLoadsController()
    {
        $this->app->get('/hello', 'hello#index');

        $client = $this->createClient();
        $crawler = $client->request('GET', '/hello');

        $this->assertTrue($client->getResponse()->isOk());
        $this->assertEquals("Hello World", $client->getResponse()->getContent());
    }
}

