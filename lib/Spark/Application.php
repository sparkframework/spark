<?php

namespace Spark;

class Application extends \Silex\Application
{
    function __construct()
    {
        parent::__construct();
        $this->register(new Core\CoreServiceProvider);
    }

    function enable($name)
    {
        $this[$name] = true;
        return $this;
    }

    function disable($name)
    {
        $this[$name] = false;
        return $this;
    }

    function group($name, callable $block)
    {
        $group = new \ArrayObject;
        $block($group, $this);

        foreach ($group as $key => $value) {
            $this["$name.$key"] = $value;
        }

        return $this;
    }
}
