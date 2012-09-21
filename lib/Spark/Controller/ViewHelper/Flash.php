<?php

namespace Spark\Controller\ViewHelper;

trait Flash
{
    function flash()
    {
        return $this->application['session']->getFlashBag();
    }
}
