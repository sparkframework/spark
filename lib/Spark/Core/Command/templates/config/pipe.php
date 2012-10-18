<?php

# JS and CSS compressors for precompilation
$app['pipe.js_compressor'] = 'yuglify_js';
$app['pipe.css_compressor'] = 'yuglify_css';

# List of assets to precompile using `spark assets:dump`
#
# By default the files "application.js" and "application.css" get 
# precompiled. To add your own files for precompilation, add them to
# this ArrayObject:
#
# $app['pipe.precompile']->append('screen.less');

# Pipe load paths, which are used to look up relative paths.
#
# The load path is a doubly linked list and defaults to:
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
#     $loadPath->push("foo");
# });
#
