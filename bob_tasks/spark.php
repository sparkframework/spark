<?php

namespace Bob\BuildConfig;

$_GLOBALS['application'] = require('config/bootstrap.php');

# Include asset tasks
require_once __DIR__ . '/assets.php';
