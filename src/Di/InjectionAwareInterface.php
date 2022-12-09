<?php

namespace One23\PhalconPhp\Di;

interface InjectionAwareInterface
{
    /**
     * Sets the dependency injector
     */
    public function setDI(DiInterface $container): void;

    /**
     * Returns the internal dependency injector
     */
    public function getDI(): DiInterface;
}
