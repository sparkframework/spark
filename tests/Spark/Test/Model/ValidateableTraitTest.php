<?php

namespace Spark\Test\Model;

use Spark\Model\ValidateableTrait;

class TestValidateable
{
    use ValidateableTrait;

    public $foo;

    static function constraints($builder)
    {
        $builder->property('foo', function($assert) {
            $assert->notBlank();
        });

        $builder->getter('bar', function($assert) {
            $assert->true();
        });
    }

    function isBar()
    {
        return (bool) $this->foo;
    }
}

class ValidateableTraitTest extends \PHPUnit_Framework_TestCase
{
    function test()
    {
        $test = new TestValidateable;

        $this->assertFalse($test->validate());

        $test->foo = "bar";

        $this->assertTrue($test->validate());
    }
}
