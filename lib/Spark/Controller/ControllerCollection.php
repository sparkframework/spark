<?php

namespace Spark\Controller;

class ControllerCollection extends \Silex\ControllerCollection
{
    function draw(callable $callback)
    {
        if ($callback instanceof \Closure) {
            $callback = $callback->bindTo($this);
        }

        $callback($this);
        return $this;
    }

    function resources($resourceName, $options = [])
    {
        $controller = @$options['controller'] ?: $resourceName;

        $this->get("/$resourceName", "$controller#index")
             ->bind("{$resourceName}_index");

        $this->get("/$resourceName/new", "$controller#new")
             ->bind("{$resourceName}_new");

        $this->get("/$resourceName/{id}", "$controller#show")
             ->bind("{$resourceName}_show");

        $this->get("/$resourceName/{id}/edit", "$controller#edit")
             ->bind("{$resourceName}_edit");

        $this->post("/$resourceName", "$controller#create")
             ->bind("{$resourceName}_create");

        $this->put("/$resourceName/{id}", "$controller#update")
             ->bind("{$resourceName}_update");

        $this->delete("/$resourceName/{id}", "$controller#destroy")
             ->bind("{$resourceName}_destroy");
    }

    function resource($resourceName, $options = [])
    {
        $controller = @$options['controller'] ?: $resourceName;

        $this->get("/$resourceName", "$controller#show");
        $this->get("/$resourceName/new", "$controller#new");
        $this->get("/$resourceName/edit", "$controller#edit");
        $this->post("/$resourceName", "$controller#create");
        $this->put("/$resourceName", "$controller#update");
        $this->delete("/$resourceName", "$controller#destroy");
    }
}
