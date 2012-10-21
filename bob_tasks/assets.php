<?php

namespace Bob\BuildConfig;

use Pipe\AssetDumper;

desc(
    'Precompile assets for deployment'
);
task('assets:precompile', function() {
    global $application;

    $env = $application['pipe']->environment;
    $dumper = new AssetDumper($application['pipe.precompile_directory']);

    foreach ($application['pipe.precompile'] as $target) {
        $asset = $env->find($target, ['bundled' => true]);

        if (!$asset) {
            failf('Asset "%s" not found.', $target);
        }

        $dumper->add($asset);
    }

    $dumper->dump();
});

