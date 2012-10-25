<?php

namespace Spark\Test\Model;

class TestModel extends \Spark\Model\Base
{}

class ModelTest extends \PHPUnit_Framework_TestCase
{
    function testEmitsNewInstance()
    {
        $called = false;

        TestModel::events()->on('newInstance', function() use (&$called) {
            $called = true;
        });

        new TestModel;

        $this->assertTrue($called);
    }

    function 
}
