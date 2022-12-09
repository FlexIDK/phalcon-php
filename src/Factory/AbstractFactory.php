<?php

namespace One23\PhalconPhp\Factory;

abstract class AbstractFactory extends AbstractConfigFactory
{
    protected array $mapper = [];

    protected array $services = [];

    /**
     * Returns the adapters for the factory
     *
     * @return string[]
     */
    abstract protected function getServices(): array;

    /**
     * Checks if a service exists and throws an exception
     */
    protected function getService(string $name)
    {
        if (!isset($this->mapper[$name])) {
            throw $this->getException("Service '{$name}' is not registered");
        }

        return $this->mapper[$name];
    }

    /**
     * Initialize services/add new services
     */
    protected function init(array $services = []): void
    {
        $adapters = $this->getServices();

        $adapters = array_merge(
            $adapters,
            $services
        );

        foreach($adapters as $name => $service) {
            $this->mapper[$name] = $service;
            unset($this->services[$name]);
        }
    }
}
