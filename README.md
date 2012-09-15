# Spark — A Classy Web Framework for Rapid Development

## Getting started

    % spark.phar my_app
    Creating new application 'my_app'...
    Done.
    % cd my_app
    % spark.phar server
    PHP Development Server started on port 8080.
    Quit with CTRL+C.

## Quick Reference

### Controllers

Controllers make your app actually do things. Controllers go into the
`controllers/` directory in your application, and are best generated
by the `generate controller` command.

    % spark.phar generate controller user

Each controller consists of actions. Each action is a public method of
the controller class.

    <?php

    namespace MyApp;

    class UserController
    {
        function index()
        {
        }
    }

The HTML goes into a "View". The view's file name gets taken from the
controller and action name. For example for the "index" action in the 
"UserController" the view "user/index.phtml" gets used.

Put this into `my_app/app/views/user/index.html.php`:

    <h1>Hello World <?= $this->name ?>!</h1>

The view has access to each property of the controller class. This
allows you to pass variables from the controller to the view.

    <?php

    namespace MyApp;
    
    class UserController
    {
        function index($name)
        {
            $this->name = $name;
        }
    }

The last part of getting our controller to do something (remotely)
useful, is to add a route. A Route connects a URI to a controller and
action. Routes are configured in `my_app/config/routes.php`.

This will do for now:

    $routes->match('/{name}', 'index#index');

That thing within the curly braces is a variable, which gets extracted
from the URI and then gets assigned the name `name`. We've previously
declared that our action needs an argument `name`, so Spark figures this
out and passes the route variable along to the action.

Last but not least, we assign the controller and action to the route.
This is done with `$controller#$action`. Controller names are converted
from `under_score` to `UnderScore` and then suffixed with `Controller`.
So `index` gets transformed to `IndexController`.

If you open <http://localhost:8080/John%20Doe> in your browser you
should see "Hello World John Doe!" in big letters.
