<?php

namespace Spark\Core;

use Silex\Application;

class ConfigBuilder extends \Pimple
{
    protected $namespace;

    function __construct($namespace = null)
    {
        parent::__construct();
        $this->namespace = $namespace;
    }

    function with(callable $block)
    {
        if ($block instanceof \Closure) {
            $block = $block->bindTo($this, $this);
        }

        return $block($this);
    }

    function enable($id)
    {
        $this[$id] = true;
        return $this;
    }

    function disable($id)
    {
        $this[$id] = false;
        return $this;
    }

    function group($name, callable $block)
    {
        $fqName = join('.', array_filter([rtrim($this->namespace, '.'), $name]));

        $config = new static($fqName);
        $config->with($block);

        $this[$fqName] = $config;
        return $this;
    }

    function flush(Application $app)
    {
        foreach ($this->keys() as $id) {
            $appId = join('.', array_filter([rtrim($this->namespace, '.'), $id]));
            $value = $this->raw($id);

            if ($value instanceof self) {
                $value->flush($app);
            } else {
                $app[$appId] = $value;
            }
        }
    }
}
