<?php

namespace One23\PhalconPhp\Factory;

use One23\PhalconPhp\Config\ConfigInterface;

abstract class AbstractConfigFactory
{
    /**
     * Checks the config if it is a valid object
     */
    protected function checkConfig(ConfigInterface|array $config): array
    {
        if ($config instanceof ConfigInterface) {
            $config = $config->toArray();
        }

        if (!is_array($config)) {
            throw $this->getException(
                "Config must be array or Phalcon\\Config\\Config object"
            );
        }

        return $config;
    }

    /**
     * Checks if the config has "adapter"
     */
    protected function checkConfigElement(array $config, string $element): array
    {
        if (!isset($config[$element])) {
            throw $this->getException(
                "You must provide '{$element}' option in factory config parameter."
            );
        }

        return $config;
    }

    /**
     * Returns the exception object for the child class
     */
    protected function getException(string $message): \Exception
    {
        $exception = $this->getExceptionClass();

        return new $exception($message);
    }

    protected function getExceptionClass(): string
    {
        return "Exception";
    }
}
