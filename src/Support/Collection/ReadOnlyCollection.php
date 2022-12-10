<?php

namespace One23\PhalconPhp\Support\Collection;

use One23\PhalconPhp\Support\Collection;

/**
 * A read only Collection object
 */
class ReadOnlyCollection extends Collection
{
    /**
     * Delete the element from the collection
     */
    public function remove(string $element): void
    {
        throw new Exception("The object is read only");
    }

    /**
     * Set an element in the collection
     */
    public function set(string $element, mixed $value): void
    {
        throw new Exception("The object is read only");
    }
}
