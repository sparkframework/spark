<?php

namespace Spark\Controller;

class ControllerCollection extends \Silex\ControllerCollection
{
    function draw(callable $callback)
    {
        $callback($this);
        return $this;
    }

    function resources($resourceName)
    {
        $this->get("/$resourceName", "$resourceName#index")
             ->bind("{$resourceName}_index");

        $this->get("/$resourceName/new", "$resourceName#new")
             ->bind("{$resourceName}_new");

        $this->get("/$resourceName/{id}", "$resourceName#show")
             ->bind("{$resourceName}_show");

        $this->get("/$resourceName/{id}/edit", "$resourceName#edit")
             ->bind("{$resourceName}_edit");

        $this->post("/$resourceName", "$resourceName#create")
             ->bind("{$resourceName}_create");

        $this->put("/$resourceName/{id}", "$resourceName#update")
             ->bind("{$resourceName}_update");

        $this->delete("/$resourceName/{id}", "$resourceName#delete")
             ->bind("{$resourceName}_delete");
    }

    function resource($resourceName)
    {
    }
}
