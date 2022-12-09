<?php

namespace One23\PhalconPhp\Annotations\Adapter;

use One23\PhalconPhp\Annotations\Reader;
use One23\PhalconPhp\Annotations\Collection;
use One23\PhalconPhp\Annotations\Reflection;
use One23\PhalconPhp\Annotations\ReaderInterface;

/**
 * This is the base class for Phalcon\Annotations adapters
 */
abstract class AbstractAdapter implements AdapterInterface
{
    protected array $annotations = [];

    protected Reader $reader;

    /**
     * Parses or retrieves all the annotations found in a class
     */
    public function get($className): Reflection
    {

        /**
         * Get the class name if it's an object
         */
        $realClassName = is_object($className)
            ? get_class($className)
            : $className;

        if (isset($this->annotations[$realClassName])) {
            return $this->annotations[$realClassName];
        }


        /**
         * Try to read the annotations from the adapter
         */
        $classAnnotations = $this->{"read"}($realClassName);

        if (is_null($classAnnotations) || $classAnnotations === false) {
            /**
             * Get the annotations reader
             */
            $reader = $this->getReader();
            $parsedAnnotations = $reader->parse($realClassName);

            $classAnnotations = new Reflection($parsedAnnotations);

            $this->annotations[$realClassName] = $classAnnotations;
            $this->{"write"}($realClassName, $classAnnotations);
        }

        return $classAnnotations;
    }

    /**
     * Returns the annotations found in a specific constant
     */
    public function getConstant(string $className, string $constantName): Collection
    {
        $constants = $this->getConstants($className);

        $constant = $constants[$constantName] ?? null;
        if (!$constant) {
            return new Collection();
        }

        return $constant;
    }

    /**
     * Returns the annotations found in all the class' constants
     */
    public function getConstants(string $className): array
    {
        /**
         * Get the full annotations from the class
         */
        $classAnnotations = $this->get($className);

        return $classAnnotations->getConstantsAnnotations();
    }

    /**
     * Returns the annotations found in a specific property
     */
    public function getProperty(string $className, string $propertyName): Collection
    {
        /**
         * Get the full annotations from the class
         */
        $classAnnotations = $this->get($className);

        $properties = $classAnnotations->getPropertiesAnnotations();

        $property = $properties[$propertyName] ?? null;
        if (!$property) {
            /**
             * Returns a collection anyways
             */
            return new Collection();
        }

        return $property;
    }

    /**
     * Returns the annotations found in all the class' properties
     */
    public function getProperties(string $className): array
    {
        /**
         * Get the full annotations from the class
         */
        $classAnnotations = $this->get($className);

        return $classAnnotations->getPropertiesAnnotations();
    }

    /**
     * Returns the annotations found in a specific method
     */
    public function getMethod(string $className, string $methodName): Collection
    {
        /**
         * Get the full annotations from the class
         */
        $classAnnotations = $this->get($className);

        $methods = $classAnnotations->getMethodsAnnotations();

        if (is_array($methods)) {
            foreach ($methods as $methodKey => $method) {
                if (!strcasecmp($methodKey, $methodName)) {
                    return $method;
                }
            }
        }

        /**
         * Returns a collection anyway
         */
        return new Collection();
    }

    /**
     * Returns the annotations found in all the class' methods
     */
    public function getMethods(string $className): array
    {
        /**
         * Get the full annotations from the class
         */
        $classAnnotations = $this->get($className);

        return $classAnnotations->getMethodsAnnotations();
    }

    /**
     * Returns the annotation reader
     */
    public function getReader(): ReaderInterface
    {
        if (!empty($this->reader)) {
            $this->reader = new Reader();
        }

        return $this->reader;
    }

    /**
     * Sets the annotations parser
     */
    public function setReader(ReaderInterface $reader)
    {
        $this->reader = $reader;
    }
}
