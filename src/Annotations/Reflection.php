<?php

namespace One23\PhalconPhp\Annotations;

/**
 * Allows to manipulate the annotations reflection in an OO manner
 */
class Reflection
{
    protected ?Collection $classAnnotations = null;

    /** @var Collection[] $constantAnnotations  */
    protected array $constantAnnotations = [];

    /** @var Collection[] $propertyAnnotations  */
    protected array $propertyAnnotations = [];

    /** @var Collection[] $methodAnnotations  */
    protected array $methodAnnotations = [];

    public function __construct(protected array $reflectionData = [])
    {
    }

    /**
     * Returns the annotations found in the class docblock
     */
    public function getClassAnnotations(): ?Collection
    {
        if (is_null($this->classAnnotations)) {
            $reflectionClass = $this->reflectionData["class"] ?? null;

            if ($reflectionClass) {
                $this->classAnnotations = new Collection($reflectionClass);
            }
        }

        return $this->classAnnotations;
    }

    /**
     * Returns the annotations found in the constants' docblocks
     *
     * @return Collection[]
     */
    public function getConstantsAnnotations(): array
    {
        $reflectionConstants = $this->reflectionData["constants"] ?? null;

        if ($reflectionConstants) {
            if (is_array($reflectionConstants) && count($reflectionConstants) > 0) {
                foreach ($reflectionConstants as $constant => $reflectionConstant) {
                    $this->constantAnnotations[$constant] = new Collection(
                        $reflectionConstant
                    );
                }
            }
        }

        return $this->constantAnnotations;
    }

    /**
     * Returns the annotations found in the properties' docblocks
     *
     * @return Collection[]
     */
    public function getPropertiesAnnotations(): array
    {
        $reflectionProperties = $this->reflectionData["properties"] ?? null;

        if ($reflectionProperties) {
            if (is_array($reflectionProperties) && count($reflectionProperties) > 0) {
                foreach ($reflectionProperties as $property => $reflectionProperty) {
                    $this->propertyAnnotations[$property] = new Collection(
                        $reflectionProperty
                    );
                }
            }
        }

        return $this->propertyAnnotations;
    }

    /**
     * Returns the annotations found in the methods' docblocks
     *
     * @return Collection[]
     */
    public function getMethodsAnnotations(): array
    {
        $reflectionMethods = $this->reflectionData["methods"] ?? null;

        if ($reflectionMethods) {
            if (is_array($reflectionMethods) && count($reflectionMethods) > 0) {
                foreach ($reflectionMethods as $methodName => $reflectionMethod) {
                    $this->methodAnnotations[$methodName] = new Collection(
                        $reflectionMethod
                    );
                }
            }
        }

        return $this->methodAnnotations;
    }

    /**
     * Returns the raw parsing intermediate definitions used to construct the
     * reflection
     */
    public function getReflectionData(): array
    {
        return $this->reflectionData;
    }
}
