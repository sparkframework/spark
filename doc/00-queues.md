# Background Processing API

Need to make an time consuming operation, but don't want to slow down
the app? Then Spark has covered your back.

Spark features a very simple and generic API for writing background
jobs. The main building block is the `queue` service. The `queue`
service features one method, that's of interest: `push`.

The `push` method takes one job (a child of `Spark\Core\Job`) and pushes
it onto the queue.

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
        # This thing is really expensive
        sleep(20);

        echo "Hello World {$this->name}!\n";
    }
}
```

You can instantiate this class like any other. Just pass all required
paramaters to the constructor.

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

## Implementing a Queue

Queues all implement the `Spark\Queue\Queue` interface. This interface
specifies three methods:

* `pop()`, which is done by the worker script (the `queue:worker`
  command), waits until a job is available and then returns it.
* `push(Job $job)`, this is done in the application and should push the
  job onto some kind of pending jobs list.
* `flush()`, which sends all jobs to the underlying service and is
  invoked by Spark after the response was sent to the user.

To override the queue implementation, set the `queue` service in your
`config/application.php`:

```php
$app['queue'] = $app->share(function() use ($app) {
    return new MyRedisQueue($app['redis']);
});
```

A simple queue which uses Redis as storage for jobs could look like
this:

```php
<?php

use Spark\Queue\Job;

class MyRedisQueue implements \Spark\Queue\Queue
{
    const QUEUE_KEY = "spark:queue";

    protected $pending;
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
```
