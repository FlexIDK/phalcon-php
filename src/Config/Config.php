<?php

namespace One23\PhalconPhp\Config;

use One23\PhalconPhp\Support\Collection;

class Config extends Collection implements ConfigInterface
{
    const DEFAULT_PATH_DELIMITER = ".";

    protected string $pathDelimiter = self::DEFAULT_PATH_DELIMITER;

    public function getPathDelimiter(): string
    {
        return $this->pathDelimiter;
    }

    /**
     * Merges a configuration into the current one
     *
     *```php
     * $appConfig = new \Phalcon\Config\Config(
     *     [
     *         "database" => [
     *             "host" => "localhost",
     *         ],
     *     ]
     * );
     *
     * $globalConfig->merge($appConfig);
     *```
     *
     * @throws Exception
     */
    public function merge(array|ConfigInterface $toMerge): ConfigInterface
    {
        $source = $this->toArray();

        $this->clear();

        if (is_array($toMerge)) {
            $result = $this->internalMerge($source, $toMerge);
        }

        elseif ($toMerge instanceof ConfigInterface) {
            $result = $this->internalMerge($source, $toMerge->toArray());
        }

        $this->init($result);

        return $this;
    }

    /**
     * Returns a value from current config using a dot separated path.
     *
     *```php
     * echo $config->path("unknown.path", "default", ".");
     *```
     */
    public function path(
        string $path,
        mixed $defaultValue = null,
        string $delimiter = null
    ): mixed {
        if ($this->has($path)) {
            return $this->get($path);
        }

        $pathDelimiter = $delimiter;
        if (empty($pathDelimiter)) {
            $pathDelimiter = $this->pathDelimiter;
        }

        $config = clone $this;
        $keys   = explode($pathDelimiter, $path);

        while (true !== empty($keys)) {
            $key = array_shift($keys);

            if (!$config->has($key)) {
                break;
            }

            if (empty($keys)) {
                return $config->get($key);
            }

            $config = $config->get($key);

            if (empty($config)) {
                break;
            }
        }

        return $defaultValue;
    }

    /**
     * Sets the default path delimiter
     */
    public function setPathDelimiter(string $delimiter = null): ConfigInterface
    {
        $this->pathDelimiter = $delimiter;

        return $this;
    }

    /**
     * Converts recursively the object to an array
     */
    public function toArray(): array
    {
        $results = [];
        $data    = parent::toArray();

        foreach($data as $key => $value) {
            if (
                is_object($value) &&
                method_exists($value, "toArray")
            ) {
                $value = $value->toArray();
            }

            $results[$key] = $value;
        }

        return $results;
    }

    /**
     * Performs a merge recursively
     */
    final protected function internalMerge(array $source, array $target): array
    {
        foreach($target as $key => $value) {
            if (
                is_array($value) &&
                isset($source[$key]) &&
                is_array($source[$key])
            ) {
                $source[$key] = $this->internalMerge($source[$key], $value);

                continue;
            }

            $source[$key] = $value;
        }

        return $source;
    }

    /**
     * Sets the collection data
     */
    protected function setData(string $element, mixed $value): void
    {
        $data    = $this->data;
        $key     = ($this->insensitive) ? mb_strtolower($element) : $element;

        $this->lowerKeys[$key] = $element;

        if (is_array($value)) {
            $data[$element] = new Config($value, $this->insensitive);
        }
        else {
            $data[$element] = $value;
        }

        $this->data = $data;
    }
}
