# Spark, ignites your ideas.

Spark is a _simple_ web framework in the spirit of Rails
which makes it simple to start web development.

## Getting started

    % php spark.phar my_app
    Creating new application 'my_app'...
    Done.
    % cd my_app
    % ./vendor/bin/bob server
    PHP Development Server started on port 8080.
    Quit with CTRL+C.

## Quick Reference

### Controllers

Controllers make your app actually do things. Controllers go into the
`controllers/` directory in your application, and are best generated
by the `generate-controller` task.

    % spark generate:controller user

Each controller consists of actions. Each action is a public method of
the controller class.

    <?php

    namespace MyApp;

    class UserController
    {
        # This gets invoked when "/user" is viewed in the browser.
        function index()
        {
        }
    }

The HTML goes into a "View". The view's file name gets taken from the
controller and action name. For example for the "index" action in the 
"UserController" the view "user/index.phtml" gets used.

The view has access to each property of the controller class. This
allows you to pass data from the controller to the view, for 
displaying it to the user of your application.

    <?php

    namespace MyApp;
    
    class UserController
    {
        function index()
        {
            $this->users = User::select();
        }
    }

### Models

Generate Models:

    % spark generate model User username:string password:string

Generated Class:

    <?php

    namespace MyApp;

    class User
    {
        use \Spark\Model\Traits\SecurePassword;

        public $username;
        public $password;
    }

