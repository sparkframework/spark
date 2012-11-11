# Background Processing API

Need to make an time consuming operation, but don't want to slow down
the app? Then Spark has covered your back.

Spark features a very simple and generic API for writing background
jobs. The main building block is the `queue` service. The `queue`
service features one method, that's interesting within the application: `push`.

The `push` method takes one job (a child of `Spark\Core\Job`) and pushes
it onto the queue. A queue worker then takes the job from the queue by
calling the `pop` method. When the worker has received a job, it calls
the `run` method.

## A Quick Example

Take for example some expensive conversion task. You don't want your
users to notice that the conversion happens.

Let's create a job class for our task. A basic class can look like this:

```php
<?php
# lib/MyApp/HelloWorldJob.php

namespace MyApp;

class HelloWorldJob extends \Spark\Core\Job
{
    protected $name;

    function __construct($name)
    {
        $this->name = $name;
    }

    function run()
    {
        # Simulate something expensive
        sleep(20);

        $this->application['logger']->info("Hello World {$this->name}!");
    }
}
```

You can instantiate this class like any other. Just pass all required
paramaters to the constructor.

You also have automatically access to the application object via the
job's `application` property.

Do this in your controller:

```php
<?php

namespace MyApp;

use Symfony\Component\HttpFoundation\Request;

class HelloController extends ApplicationController
{
    function indexAction($name)
    {
        $job = new HelloWorldJob($name);

        $this->application['queue']->push($job);
    }
}
```

The last bit is to start the worker for background jobs. To do this,
simply invoke `./vendor/bin/spark queue:worker` in your application
directory.

If you request your controller in your browser, with a name say "John Doe", the page renders quick,
despite the fact that our job needs 20 seconds to run.

Then look at the console window where you run the `queue:worker`
command. You should see "Hello World John Doe!".

## Going concurrent

- - -
Concurrent execution of jobs is only available on __*nix__ platforms,
like Linux or OSX.
- - -

One thing you may have noticed is, that the worker only processes one
job at a time. 

This has some pretty big drawbacks:

* A job gone wrong is able to kill the worker, and there's no automatic
  respawn of workers.
* Only one CPU core is used for processing workers.

If you happen to have a \*nix OS and the `pcntl` extension enabled, you
can use the `-c` flag on the `queue:worker` command and set it to
something greater than zero to enable the preforking worker.

This spawns four worker processes, allowing it to process four different
jobs at a time:

    % ./vendor/bin/spark queue:worker -c 4

The preforking worker spawns a pool of _n_ child processes (the number is
used from the `-c` flag), which each call `pop` on the queue.

The master process then attempts to keep _n_ child processes around,
starting new ones and killing stale ones as it becomes necessary.

## Implementing a Queue

The queuing system is built on top of [Kue][], an abstraction layer on
top of multiple job queue systems.

[Kue]: https://github.com/CHH/kue

Queues all implement the `Kue\Queue` interface. This interface
specifies three methods:

* `pop()`, which is done by the worker script (the `queue:worker`
  command), waits until a job is available and then returns it.
* `push(Job $job)`, this is done in the application and should push the
  job onto some kind of pending jobs list.
* `flush()`, is invoked by Spark after the response was sent to the user. Can be used to send
  jobs only to the queue at the end of the request, and use more
  efficient transportation methods, like bulk requests.

To override the queue implementation, set the `queue` service in your
`config/application.php`:

```php
$app['queue'] = $app->share(function() use ($app) {
    return new MyRedisQueue($app['redis']);
});
```

A simple queue, which uses Redis as storage for jobs, could look like
this (but see [Kue\\RedisQueue][] if you need this for real):

[Kue\\RedisQueue]: https://github.com/CHH/kue/tree/master/lib/Kue/RedisQueue.php

```php
<?php

use Kue\Job;
use Kue\Queue;

class MyRedisQueue implements Queue
{
    const QUEUE_KEY = "spark:queue";

    protected $redis;

    function __construct(\Redis $redis)
    {
        $this->redis = $redis;
    }

    function pop()
    {
        $response = $this->redis->blPop(self::QUEUE_KEY, 10);

        if ($response) {
            list($list, $serializedJob) = $response;

            $job = unserialize($serializedJob);
            return $job;
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
```
