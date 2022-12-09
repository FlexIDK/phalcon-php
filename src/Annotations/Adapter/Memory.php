<?php

namespace One23\PhalconPhp\Annotations\Adapter;

use One23\PhalconPhp\Annotations\Reflection;

/**
 * Stores the parsed annotations in memory. This adapter is the suitable
 * development/testing
 */
class Memory extends AbstractAdapter
{
    protected mixed $data;

    /**
     * Reads parsed annotations from memory
     */
    public function read(string $key): Reflection|bool
    {
        return $this->data[strtolower($key)] ?? false;
    }

    /**
     * Writes parsed annotations to memory
     */
    public function write(string $key, Reflection $data): void
    {
        $lowercasedKey = strtolower($key);
        $this->data[$lowercasedKey] = $data;
    }
}
