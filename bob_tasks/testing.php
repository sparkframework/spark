<?php

namespace Bob\BuildConfig;

copyTask('phpunit.dist.xml', 'phpunit.xml');

desc('Runs all tests');
task('test', ['test:unit', 'test:integration']);

desc('Runs only integration tests');
task('test:integration', ['phpunit.xml'], function() {
    info("Running integration tests...");
    sh("phpunit tests/integration");
});

desc('Runs only unit tests');
task('test:unit', ['phpunit.xml'], function() {
    info("Running unit tests...");
    sh("phpunit tests/unit");
});

