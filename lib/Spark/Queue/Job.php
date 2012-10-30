<?php

namespace Spark\Queue;

/**
 * Interface for jobs
 *
 * @author Christoph Hochstrasser <christoph.hochstrasser@gmail.com>
 */
interface Job
{
    /**
     * Invoked by the worker script
     *
     * @return void
     */
    function run();
}

