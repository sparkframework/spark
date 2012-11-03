<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../config/bootstrap.php';

\Spark\Core\WebTestCase::setApplication($app);

