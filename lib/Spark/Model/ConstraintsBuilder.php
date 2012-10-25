<?php

namespace Spark\Model;

use Symfony\Component\Validator\Mapping\ClassMetadata;

class ConstraintsBuilder
{
    protected $metadata;

    function __construct(ClassMetadata $metadata)
    {
        $this->metadata = $metadata;
    }

    function property($property, callable $callback)
    {
        $asserts = new AssertBuilder;
        $callback($asserts);

        foreach ($asserts->constraints as $constraint) {
            $this->metadata->addPropertyConstraint($property, $constraint);
        }

        return $this;
    }

    function getter($property, callable $callback)
    {
        $asserts = new AssertBuilder;
        $callback($asserts);

        foreach ($asserts->constraints as $constraint) {
            $this->metadata->addGetterConstraint($property, $constraint);
        }

        return $this;
    }
}
