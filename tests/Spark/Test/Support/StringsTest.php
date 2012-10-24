<?php

namespace Spark\Test\Support;

use Spark\Support\Strings;

class StringsTest extends \PHPUnit_Framework_TestCase
{
    function testCamelize()
    {
        $this->assertEquals('fooBarBaz', Strings::camelize('foo_bar_baz'));
        $this->assertEquals('FooBarBaz', Strings::camelize('foo_bar_baz', true));
    }
}

