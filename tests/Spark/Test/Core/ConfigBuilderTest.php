<?php

namespace Spark\Test\Core;

use Silex\Application;
use Spark\Core\ConfigBuilder;

class ConfigBuilderTest extends \PHPUnit_Framework_TestCase
{
    protected $app;

    function setup()
    {
        $this->app = new Application;
    }

    function testEnableDisable()
    {
        $builder = new ConfigBuilder;
        $builder->enable('foo');
        $builder->disable('bar');

        $builder->flush($this->app);

        $this->assertTrue($this->app['foo']);
        $this->assertFalse($this->app['bar']);
    }

    function testGroup()
    {
        $builder = new ConfigBuilder;

        $builder->group('foo', function($g) {
            $g['bar'] = 'baz';
        });

        $this->assertEquals('baz', $builder['foo']['bar']);
    }

    function testNamespace()
    {
        $builder = new ConfigBuilder('foo');
        $builder->enable('bar');

        $builder->flush($this->app);

        $this->assertTrue($this->app['foo.bar']);
    }
}
