<?php

namespace Spark\Core\Generator;

abstract class Base
{
    abstract function generate();

    function createDirectory($directories)
    {
        $directories = (array) $directories;

        foreach ($directories as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
        }
    }

    function touch($file)
    {
    }
}
