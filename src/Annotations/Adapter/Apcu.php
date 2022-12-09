<?php

namespace One23\PhalconPhp\Annotations\Adapter;

use One23\PhalconPhp\Annotations\Reflection;

/**
 * Stores the parsed annotations in memory. This adapter is the suitable
 * development/testing
 */
class Apcu extends AbstractAdapter
{
    protected string $prefix = "";

    protected int $ttl = 172800;

    public function __construct(array $options = [])
    {
        $prefix = $options["prefix"] ?? null;
        if ($prefix && is_string($prefix)) {
            $this->prefix = $prefix;
        }

        $ttl = $options["lifetime"] ?? null;
        if (is_int($ttl) && $ttl > 0) {
            $this->ttl = $ttl;
        }
    }

    /**
     * Reads parsed annotations from memory
     */
    public function read(string $key): Reflection|bool
    {
        return apcu_fetch(
            strtolower(
                "_PHAN" . $this->prefix . $key
            )
        );
    }

    /**
     * Writes parsed annotations to memory
     */
    public function write(string $key, Reflection $data): bool
    {
        return apcu_store(
            strtolower(
                "_PHAN" . $this->prefix . $key
            ),
            $data,
            $this->ttl
        );
    }
}
