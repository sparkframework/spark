<?php

namespace Bob\BuildConfig;

use Pipe\Manifest;

desc(
    'Precompile assets for deployment'
);
task('assets:precompile', function() {
    $application = require 'config/bootstrap.php';

    $env = $application['pipe']->environment;

    $manifest = new Manifest($env, $application['pipe.precompile_directory'] . '/manifest.json');
    $manifest->compress = true;
    $manifest->compile((array) $application['pipe.precompile']);
});

