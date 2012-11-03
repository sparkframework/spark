<?php

namespace Bob\BuildConfig;

require_once(__DIR__ . '/vendor/spark/spark/bob_tasks/spark.php');

copyTask('phpunit.dist.xml', 'phpunit.xml');

task('test', ['test:unit', 'test:integration']);

task('test:integration', ['phpunit.xml'], function() {
    sh("phpunit tests/integration");
});

task('test:unit', ['phpunit.xml'], function() {
    sh("phpunit tests/unit");
});

