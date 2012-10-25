<?php

namespace Spark\Model;

use Evenement\EventEmitterInterface;

interface BehaviorInterface
{
    /**
     * Gets called when the behavior is registered via
     * Model::register()
     *
     * @param string $class Class name on which this behavior was
     * registered
     * @param EventEmitterInterface $events The model's event emitter
     * @return void
     */
    function register($class, EventEmitterInterface $events);
}
