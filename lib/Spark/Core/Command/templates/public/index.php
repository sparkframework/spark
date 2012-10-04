<?php

if (php_sapi_name() === 'cli-server') {
    $_SERVER["SPARK_ENV"] = "development";
    $filename = __DIR__.preg_replace('#(\?.*)$#', '', $_SERVER['REQUEST_URI']);

    if (is_file($filename)) {
        return false;
    }
}

require_once(__DIR__ . "/../vendor/autoload.php");

$app = require_once(__DIR__ . "/../config/bootstrap.php");
$app->run();

