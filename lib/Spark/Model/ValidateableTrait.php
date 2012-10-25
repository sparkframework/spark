<?php

namespace Spark\Model;

use Symfony\Component\Validator\Validator;
use Symfony\Component\Validator\Mapping\ClassMetadataFactory;
use Symfony\Component\Validator\Mapping\Loader\StaticMethodLoader;
use Symfony\Component\Validator\ConstraintValidatorFactory;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * Include this trait in a class, to make it validateable.
 */
trait ValidateableTrait
{
    private static $validator;

    /**
     * Setup validation constraints with a simple DSL
     *
     * @return void
     */
    static function constraints(ConstraintBuilder $validate)
    {}

    /**
     * Evaluates the ConstraintBuilder and returns the metadata
     *
     * @param ClassMetadata $metadata
     * @return ClassMetadata
     */
    static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $builder = new ConstraintsBuilder($metadata);
        static::constraints($builder);

        return $metadata;
    }

    /**
     * Creates a validator for this class
     *
     * @return Validator
     */
    private static function validator()
    {
        if (null === static::$validator) {
            static::$validator = new Validator(
                new ClassMetadataFactory(new StaticMethodLoader),
                new ConstraintValidatorFactory
            );
        }

        return static::$validator;
    }

    /**
     * Validates the class.
     *
     * @param array $options
     *
     * @return boolean
     */
    function validate($options = [])
    {
        $groups = @$options['groups'] ?: [];

        $this->errors = static::validator()->validate($this, $groups);

        return $this->errors->count() === 0;
    }

    /**
     * Returns a list of errors.
     *
     * @return \Symfony\Component\Validator\ConstraintViolationList
     */
    function getErrors()
    {
        return $this->errors;
    }
}
