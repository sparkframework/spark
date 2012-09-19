<?php

namespace Spark\Controller;

class ControllerCollection extends \Silex\ControllerCollection
{
    function draw(callable $callback)
    {
        $callback($this);
        return $this;
    }
}
