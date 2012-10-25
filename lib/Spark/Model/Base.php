<?php

namespace Spark\Model;

use Evenement\EventEmitter;

abstract class Base
{
    use ValidateableTrait;

    protected $identifier;

    static protected $events;
    static protected $behaviors = [];

    static function register(BehaviorInterface $behavior)
    {
        $this->behaviors[] = $behavior;
        $behavior->register(get_called_class(), static::events());
    }

    static function events()
    {
        if (null === static::$events) {
            static::$events = new EventEmitter;
        }

        return static::$events;
    }

    function __construct($properties = [])
    {
        foreach ($properties as $property => $value) {
            $this->$property = $value;
        }

        $this->__init();

        static::events()->emit('newInstance', [$this]);
    }

    function __init()
    {}

    function save()
    {
        static::events()->emit('before:save', [$this]);

        if ($this->identifier) {
            static::events()->emit('update', [$this]);
        } else {
            static::events()->emit('create', [$this]);
        }

        static::events()->emit('after:save', [$this]);
    }

    function delete()
    {
    }
}
