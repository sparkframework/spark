# Spark Quickstart

## Install

First get the `spark.phar` file:

    % wget http://getspark.org/spark.phar

Then start a new project by calling the `spark.phar` with the
project name:

    % php spark.phar MyApp

This generates a new directory "MyApp".

Now we start a development server for our new app:

    % cd MyApp
    % ./scripts/server
    Server running on "localhost:3000"

Next visit <localhost:3000> in your web browser. You should see
a friendly page congratulating you for getting set up.

## Your first controller

Spark integrates with the [Bob][] command line tool to provide
you some conveniences.

[bob]: https://github.com/CHH/bob

One of these conveniences is that it's able to generate commonly
needed files for development of your application.

Generally all tasks for code generation are prefixed with `generate:`.
So to generate a controller use `generate:controller`:

    % ./vendor/bin/bob generate:controller IndexController

Now you should have a file `IndexController.php` in `controllers/MyApp`:

    <?php
    
    namespace MyApp;

    class IndexController extends ApplicationController
    {
    }

