<?php

namespace Spark\Core;

use Silex\Application;

interface ApplicationAware
{
    function setApplication(Application $application);
}
