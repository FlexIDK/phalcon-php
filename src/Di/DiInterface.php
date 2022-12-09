<?php

namespace One23\PhalconPhp\Di;

use ArrayAccess;

interface DiInterface extends ArrayAccess
{
    /**
     * Attempts to register a service in the services container
     * Only is successful if a service hasn't been registered previously
     * with the same name
     */
    public function attempt(string $name, mixed $definition, bool $shared = false): ServiceInterface|bool;

    /**
     * Resolves the service based on its configuration
     */
    public function get(string $name, mixed $parameters = null): mixed;

    /**
     * Return the last DI created
     */
    public static function getDefault(): ?DiInterface;

    /**
     * Returns a service definition without resolving
     */
    public function getRaw(string $name): mixed;

    /**
     * Returns the corresponding Phalcon\Di\Service instance for a service
     */
    public function getService(string $name): ServiceInterface;

    /**
     * Return the services registered in the DI
     *
     * @return ServiceInterface[];
     */
    public function getServices(): array;

    /**
     * Returns a shared service based on their configuration
     */
    public function getShared(string $name, mixed $parameters = null): mixed;

    /**
     * Check whether the DI contains a service by a name
     */
    public function has(string $name): bool;

    /**
     * Removes a service in the services container
     */
    public function remove(string $name): void;

    /**
     * Resets the internal default DI
     */
    public static function reset(): void;

    /**
     * Registers a service in the services container
     */
    public function set(string $name, mixed $definition, bool $shared = false): ServiceInterface;

    /**
     * Set a default dependency injection container to be obtained into static
     * methods
     */
    public static function setDefault(DiInterface $container): void;

    /**
     * Sets a service using a raw Phalcon\Di\Service definition
     */
    public function setService(string $name, ServiceInterface $rawDefinition): ServiceInterface;

    /**
     * Registers an "always shared" service in the services container
     */
    public function setShared(string $name, mixed $definition): ServiceInterface;
}
