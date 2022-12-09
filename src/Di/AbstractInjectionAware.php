<?php

namespace One23\PhalconPhp\Di;

abstract class AbstractInjectionAware implements InjectionAwareInterface
{

    /**
     * Dependency Injector
     */
    protected DiInterface $container;

    /**
     * Returns the internal dependency injector
     */
    public function getDI(): DiInterface
    {
        return $this->container;
    }

    /**
     * Sets the dependency injector
     */
    public function setDI(DiInterface $container): void
    {
        $this->container = $container;
    }

}
