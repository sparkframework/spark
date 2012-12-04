<?php

use Spark\Exception\RuntimeException;

if (version_compare(PHP_VERSION, '5.4.0') === -1) {
    throw new RuntimeException(sprintf(
        'Spark needs at least PHP 5.4.0, "%s" found.', PHP_VERSION
    ));
}

