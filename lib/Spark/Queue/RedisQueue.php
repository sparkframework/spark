<?php

namespace Spark\Queue;

class RedisQueue implements Queue
{
    const QUEUE_KEY = "spark:queue";

    protected $redis;

    function __construct(\Redis $redis)
    {
        $this->redis = $redis;
    }

    function pop()
    {
        for (;;) {
            $response = $this->redis->blPop(self::QUEUE_KEY, 10);

            if ($response) {
                list($list, $serializedJob) = $response;

                $job = unserialize($serializedJob);
                return $job;
            }
        }
    }

    function push(Job $job)
    {
        $this->redis->rPush(self::QUEUE_KEY, serialize($job));
    }

    function flush()
    {
        # We send jobs directly in `push`, so we don't need to flush
    }
}
