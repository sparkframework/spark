<?php

# JS and CSS compressors for precompilation
$app['pipe.js_compressor'] = 'yuglify_js';
$app['pipe.css_compressor'] = 'yuglify_css';

# 'pipe.prefix' is prepended to all static links when precompiled files
# are used. Make sure this directory contains the precompiled files and
# manifest and is servable by your web server.
#
# This is the default setting:
#
$app['pipe.prefix'] = "/assets";

#
# If you use a CDN (make sure all files including the 'manifest.json' is 
# deployed there):
#
# $app['pipe.prefix'] = "http://assets.myapp.com";

# Path where precompiled assets are generated. Assets URLs are later 
# generated with 'pipe.prefix' prefixed. 
$app['pipe.precompile_directory'] = function() use ($app) {
    return "{$app['spark.root']}/public/assets";
};

# Location of the manifest file, which maps logical paths like 
# 'application.js' to the path including the digest,
# like 'application-adc83b19e793491b1c6ea0fd8b46cd9f32e592fc.js'
$app['pipe.manifest'] = function() use ($app) {
    return "{$app['pipe.precompile_directory']}/manifest.json";
};

# List of assets to precompile using `spark assets:dump`
#
# By default the files "application.js" and "application.css" get 
# precompiled. To add your own files for precompilation, add them to
# this list:
#
# $app['pipe.precompile'] = $app->extend('pipe.precompile', function($assets) {
#     $assets[] = "mobile.coffee";
#
#     return $assets;
# });

# Pipe load paths, which are used to look up relative paths.
#
# The load path is an array and defaults to:
#
# 1. app/assets/images/
# 2. app/assets/javascripts/
# 3. app/assets/vendor/javascripts/
# 4. app/assets/stylesheets/
# 5. app/assets/vendor/stylesheets/
#
# To add your own load paths, simply extend the 'pipe.load_path' key:
#
# $app['pipe.load_path'] = $app->extend('pipe.load_path', function($loadPath) {
#     $loadPath[] = "/my/custom/dir";
#
#     return $loadPath;
# });
