<?php

namespace One23\PhalconPhp\Di;

interface ServiceInterface
{
    /**
     * Returns the service definition
     */
    public function getDefinition(): mixed;

    /**
     * Returns a parameter in a specific position
     */
    public function getParameter(int $position): array;

    /**
     * Returns true if the service was resolved
     */
    public function isResolved(): bool;

    /**
     * Check whether the service is shared or not
     */
    public function isShared(): bool;

    /**
     * Resolves the service
     */
    public function resolve(array $parameters = null, DiInterface $container = null);

    /**
     * Set the service definition
     */
    public function setDefinition($definition);

    /**
     * Changes a parameter in the definition without resolve the service
     */
    public function setParameter(int $position, array $parameter = []): ServiceInterface;

    /**
     * Sets if the service is shared or not
     */
    public function setShared(bool $shared);
}
