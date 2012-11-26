<?php

use Sami\Sami;
use Symfony\Component\Finder\Finder;

$iterator = Finder::create()
    ->files()
    ->name('*.php')
    ->in(__DIR__ . '/lib/')
;

return new Sami($iterator, [
    'title' => 'Spark Framework API',
    'build_dir' => __DIR__ . '/_build/api',
    'cache_dir' => __DIR__ . '/_build/api_cache',
    'default_opened_level' => 2
]);

