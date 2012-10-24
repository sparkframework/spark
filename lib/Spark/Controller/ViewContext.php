<?php

namespace Spark\Controller;

use Symfony\Component\HttpFoundation\Response;

class ViewContext
{
    use ViewHelper\Assets;
    use ViewHelper\Flash;
    use ViewHelper\Uri;
    use ViewHelper\Render;

    public $format = "html";
    public $context;
    public $options = [];
    public $script;
    public $parent;
    public $response;

    /** @var array Blocks and their contents */
    protected $blocks = [];

    /** @var Application */
    protected $application;

    /** Holds stack of names of currently capturing blocks. */
    protected $capturing;

    function __construct(\Silex\Application $app)
    {
        $this->application = $app;
        $this->context = (object) [];
        $this->capturing = new \SplStack;
    }

    /**
     * Assigns the string to the block.
     *
     * @api
     *
     * @param string $block Block name
     * @param string $content Content for the block
     *
     * @return ViewContext
     */
    function setBlock($block, $content)
    {
        $this->blocks[$block] = $content;
        return $this;
    }

    /**
     * Starts capturing content into a block
     *
     * @api
     *
     * @param string $name Name of the block.
     * @param callable $block Optional, output of the callback gets 
     * captured into the block.
     *
     * @return void
     */
    function contentFor($name, callable $block = null)
    {
        $this->capturing->push($name);
        ob_start();

        if ($block !== null) {
            $block();
            $this->endContentFor();
        }
    }

    /**
     * Stops capturing started with `contentFor()` and saves the 
     * captured content to the block name given to `contentFor()`.
     *
     * @return void
     */
    function endContentFor()
    {
        $block = $this->capturing->pop();

        if (null === $block) {
            return;
        }

        $content = ob_get_clean();
        $this->setBlock($block, $content);
    }

    /**
     * Returns the content of the block
     *
     * @param string $name Block name
     *
     * @return string
     */
    function block($name)
    {
        if (isset($this->blocks[$name])) {
            return (string) $this->blocks[$name];
        }
    }

    function blocks()
    {
        return array_map('strval', $this->blocks);
    }

    function __get($property)
    {
        if (isset($this->context->$property)) {
            if (is_callable($this->context->$property)) {
                $callback = $this->context->$property;
                return $this->context->$property = $callback($this);
            }

            return $this->context->$property;
        }
    }

    function __call($method, $argv)
    {
        if (isset($this->$method)) {
            return call_user_func_array($this->$method, $argv);
        }

        throw new \BadMethodCallException(sprintf(
            'Call to undefined method %s::%s()', get_called_class(), $method
        ));
    }
}

