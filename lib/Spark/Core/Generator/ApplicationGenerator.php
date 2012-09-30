<?php

namespace Spark\Core\Generator;

class Application extends Base
{
    function generate()
    {
        $app = "foo";

        $this->createDirectory($app);
    }
}
