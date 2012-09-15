<?php

namespace Spark;

class Application extends \Silex\Application
{
    function __construct()
    {
        parent::__construct();
        $this->register(new CoreServiceProvider);
    }
}
