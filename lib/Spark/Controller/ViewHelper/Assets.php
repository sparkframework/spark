<?php

namespace Spark\Controller\ViewHelper;

trait Assets
{
    function assetLink($logicalPath)
    {
        return $this->application['pipe']->assetLink($logicalPath);
    }

    function assetLinkTag($logicalPath)
    {
        return $this->application['pipe']->assetLinkTag($logicalPath);
    }
}
