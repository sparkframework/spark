<?php

namespace Spark\Support;

class Strings
{
    static function camelize($string, $upperCamelCase = false)
    {
        $camelized = str_replace(' ', '', ucwords(str_replace('_', ' ', $string)));

        if ($upperCamelCase) {
            return $camelized;
        }

        return lcfirst($camelized);
    }
}
