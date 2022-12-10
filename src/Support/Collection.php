<?php

namespace One23\PhalconPhp\Support;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use JsonSerializable;
use Serializable;
use Traversable;
use One23\PhalconPhp\Support\Collection\CollectionInterface;

class Collection implements
    ArrayAccess,
    CollectionInterface,
    Countable,
    IteratorAggregate,
    JsonSerializable,
    Serializable
{
    protected array $data = [];

    protected array $lowerKeys = [];

    /**
     * Collection constructor.
     */
    public function __construct(array $data = [], protected bool $insensitive = true)
    {
        $this->init($data);
    }

    /**
     * Magic getter to get an element from the collection
     */
    public function __get(string $element): mixed
    {
        return $this->get($element);
    }

    /**
     * Magic isset to check whether an element exists or not
     */
    public function __isset(string $element): bool
    {
        return $this->has($element);
    }

    /**
     * Magic setter to assign values to an element
     */
    public function __set(string $element, mixed $value): void
    {
        $this->set($element, $value);
    }

    /**
     * Magic unset to remove an element from the collection
     */
    public function __unset(string $element): void
    {
        $this->remove($element);
    }

    /**
     * Clears the internal collection
     */
    public function clear(): void
    {
        $this->data      = [];
        $this->lowerKeys = [];
    }

    /**
     * Count elements of an object.
     * See [count](https://php.net/manual/en/countable.count.php)
     */
    public function count(): int
    {
        return count($this->data);
    }

    /**
     * Get the element from the collection
     */
    public function get(
        string $element,
        mixed $defaultValue = null,
        string $cast = null
    ): mixed {
        $element = $this->processKey($element);

        /**
         * If the key is not set, return the default value
         */
        if (!isset($this->lowerKeys[$element])) {
            return $defaultValue;
        }

        $key = $this->lowerKeys[$element];
        $value = $this->data[$key];

        /**
         * If the key is set and is `null` then return the default
         * value also. This aligns with 3.x behavior
         */
        if (is_null($value)) {
            return $defaultValue;
        }

        if (!is_null($cast)) {
            settype($value, $cast);
        }

        return $value;
    }

    /**
     * Returns the iterator of the class
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->data);
    }

    /**
     * Return the keys as an array
     */
    public function getKeys(bool $insensitive = true): array
    {
        $collection = ($insensitive === true) ? $this->lowerKeys : $this->data;

        return array_keys($collection);
    }

    /**
     * Return the values as an array
     */
    public function getValues(): array
    {
        return array_values($this->data);
    }

    /**
     * Determines whether an element is present in the collection.
     */
    public function has(string $element): bool
    {
        $element = $this->processKey($element);

        return isset($this->lowerKeys[$element]);
    }

    /**
     * Initialize internal array
     */
    public function init(array $data = []): void
    {
        foreach($data as $key => $value) {
            $this->setData($key, $value);
        }
    }

    /**
     * Specify data which should be serialized to JSON
     * See [jsonSerialize](https://php.net/manual/en/jsonserializable.jsonserialize.php)
     */
    public function jsonSerialize(): array
    {
        $records = [];

        foreach($this->data as $key => $value) {
            if (
                is_object($value) &&
                method_exists($value, "jsonSerialize")
            ) {
                $records[$key] = $value->{"jsonSerialize"}();
            }
            else {
                $records[$key] = $value;
            }

        }

        return $records;
    }

    /**
     * Whether a offset exists
     * See [offsetExists](https://php.net/manual/en/arrayaccess.offsetexists.php)
     */
    public function offsetExists(mixed $element): bool
    {
        return $this->has((string)$element);
    }

    /**
     * Offset to retrieve
     * See [offsetGet](https://php.net/manual/en/arrayaccess.offsetget.php)
     */
    public function offsetGet(mixed $element): mixed
    {
        return $this->get((string)$element);
    }

    /**
     * Offset to set
     * See [offsetSet](https://php.net/manual/en/arrayaccess.offsetset.php)
     */
    public function offsetSet(mixed $element, mixed $value): void
    {
        $this->set((string)$element, $value);
    }

    /**
     * Offset to unset
     * See [offsetUnset](https://php.net/manual/en/arrayaccess.offsetunset.php)
     */
    public function offsetUnset(mixed $element): void
    {
        $this->remove((string)$element);
    }

    /**
     * Delete the element from the collection
     */
    public function remove(string $element): void
    {
        if (!$this->has($element)) {
            return;
        }

        $element   = $this->processKey($element);
        $data      = $this->data;
        $lowerKeys = $this->lowerKeys;
        $key       = $lowerKeys[$element];

        unset($lowerKeys[$element]);
        unset($data[$key]);

        $this->data      = $data;
        $this->lowerKeys = $lowerKeys;
    }

    /**
     * Set an element in the collection
     */
    public function set(string $element, mixed $value): void
    {
        $this->setData($element, $value);
    }

    /**
     * String representation of object
     * See [serialize](https://php.net/manual/en/serializable.serialize.php)
     */
    public function serialize(): string
    {
        return serialize($this->toArray());
    }

    /**
     * Returns the object in an array format
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * Returns the object in a JSON format
     *
     * The default string uses the following options for json_encode
     *
     * `JSON_HEX_TAG`, `JSON_HEX_APOS`, `JSON_HEX_AMP`, `JSON_HEX_QUOT`,
     * `JSON_UNESCAPED_SLASHES`
     *
     * See [rfc4627](https://www.ietf.org/rfc/rfc4627.txt)
     */
    public function toJson(int $options = 4194383): string
    {
        $result = $this->phpJsonEncode($this->jsonSerialize(), $options);

        if (false === $result) {
            $result = "";
        }

        return $result;
    }

    /**
     * Constructs the object
     * See [unserialize](https://php.net/manual/en/serializable.unserialize.php)
     */
    public function unserialize(string $serialized): void
    {
        $data = unserialize($serialized);

        $this->init($data);
    }

    public function __serialize(): array
    {
        return $this->toArray();
    }

    public function __unserialize(array $data): void
    {
        $this->init($data);
    }

    /**
     * Internal method to set data
     */
    protected function setData(string $element, mixed $value): void
    {
        $key                    = $this->processKey($element);
        $this->data[$element]  = $value;
        $this->lowerKeys[$key] = $element;
    }

    protected function phpJsonEncode(mixed $value, int $flags = 0, int $depth = 512): string|false
    {
        return json_encode($value, $flags, $depth);
    }

    /**
     * Checks if we need insensitive keys and if so, converts the element to
     * lowercase
     */
    protected function processKey(string $element): string
    {
        if ($this->insensitive) {
            return mb_strtolower($element);
        }

        return $element;
    }
}
