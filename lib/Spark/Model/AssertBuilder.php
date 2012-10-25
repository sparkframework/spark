<?php

namespace Spark\Model;

class AssertBuilder
{
    public $constraints = [];

    function __call($constraint, $arguments = [])
    {
        $class = "\\Symfony\\Component\\Validator\\Constraints\\" . ucfirst($constraint);
        $options = array_shift($arguments);

        $this->constraints[] = $constraint = new $class($options);

        return $constraint;
    }
}
