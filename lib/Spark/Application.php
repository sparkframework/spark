<?php

namespace Spark;

class Application extends \Silex\Application
{
    # Current version of the application structure
    const CURRENT_APP_VERSION = 1;

    function __construct()
    {
        parent::__construct();
        $this->register(new Core\CoreServiceProvider);
    }
}
