# Controllers

Controllers are classes which get called when the route they're
associated with gets triggered in your application.

They live in the app's `controllers/` directory and are in the app's
namespace.

Example:

```php
<?php

namespace MyApp;

class AuthenticationConcern extends \Spark\Controller\Concern
{
    function registered($controller)
    {
        $controller->beforeFilter([$this, "checkAuth"]);
    }

    private function checkAuth()
    {
    }
}

class IndexController extends ApplicationController
{
    function init()
    {
        $this->beforeFilter("checkAuth");

        $this->useConcern("AuthenticationConcern");
    }

    private function checkAuth()
    {}

    function hello($name)
    {
        $this->name = $name;
    }
}

# View script "index/hello.phtml"
<h1>Hello World <?= $this->name ?>!</h1>

# config/routes.php
...
$routes->get("/hello/{name}", "index#hello");
```

If you then go to `http://localhost:3000/hello/John` you should see
`Hello World John` in big bold letters.

