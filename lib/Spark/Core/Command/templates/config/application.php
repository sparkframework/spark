<?php

namespace __AppName__;

# The "$app" variable is kind of special. It's available in all PHP 
# files beneath "config/".

$app['spark.app.name'] = '__AppName__';
# $app['spark.view_context_class'] = '\__AppName__\ViewContext';

# All your controller and model classes are loaded from this namespaces. By
# default its set to your app name.
#
# $app['spark.default_module'] = "__AppName__";

# This is the base class which should be extended by all your controllers.
class ApplicationController extends \Spark\Controller\Base
{
    # Include Action Helper traits here to make them available
    # in all your controllers.
}

class ViewContext extends \Spark\Controller\ViewContext
{
    # Include your custom view helpers here.
}

# Asset Pipeline configuration
require __DIR__ . '/pipe.php';

