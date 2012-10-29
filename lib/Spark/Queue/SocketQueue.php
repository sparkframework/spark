<?php

namespace Spark\Queue;

use SplQueue;

class SocketQueue implements Queue
{
    private $queue;
    private $socket;

    private $client;

    function __construct($socket)
    {
        $this->socket = $socket;

        $this->queue = new SplQueue;
        $this->queue->setIteratorMode(SplQueue::IT_MODE_FIFO | SplQueue::IT_MODE_DELETE);
    }

    function push(Job $job)
    {
        $this->queue->push($job);
    }

    function flush()
    {
        if ($this->queue->count() === 0) {
            return;
        }

        $client = $this->client();

        foreach ($this->queue as $job) {
            $data = serialize($job);
            fwrite($client, "$data\r\n");
        }

        fclose($client);
    }

    private function client()
    {
        if (null === $this->client) {
            $this->client = stream_socket_client($this->socket, $errno, $errstr);

            if ($this->client === false) {
                throw new \InvalidArgumentException(sprintf(
                    'Connecting to socket failed: %s', $errstr
                ));
            }
        }

        return $this->client;
    }
}

