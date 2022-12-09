<?php

namespace One23\PhalconPhp\Annotations\Adapter;

use One23\PhalconPhp\Annotations\Reflection;
use One23\PhalconPhp\Annotations\Collection;
use One23\PhalconPhp\Annotations\ReaderInterface;

/**
 * This interface must be implemented by adapters in Phalcon\Annotations
 */
interface AdapterInterface
{
    /**
     * Parses or retrieves all the annotations found in a class
     */
    public function get(string $className): Reflection;

    /**
     * Returns the annotations found in a specific constant
     */
    public function getConstant(string $className, string $constantName): Collection;

    /**
     * Returns the annotations found in all the class' constants
     */
    public function getConstants(string $className): array;

    /**
     * Returns the annotations found in a specific property
     */
    public function getProperty(string $className, string $propertyName): Collection;

    /**
     * Returns the annotations found in all the class' methods
     */
    public function getProperties(string $className): array;

    /**
     * Returns the annotations found in a specific method
     */
    public function getMethod(string $className, string $methodName): Collection;

    /**
     * Returns the annotations found in all the class' methods
     */
    public function getMethods(string $className): array;

    /**
     * Returns the annotation reader
     */
    public function getReader(): ReaderInterface;

    /**
     * Sets the annotations parser
     */
    public function setReader(ReaderInterface $reader);
}
