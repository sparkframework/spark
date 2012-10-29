<?php

namespace Spark\Queue;

interface Queue
{
    function push(Job $job);
    function flush();
}

