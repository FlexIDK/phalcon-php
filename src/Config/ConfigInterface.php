<?php

namespace One23\PhalconPhp\Config;

use One23\PhalconPhp\Support\Collection\CollectionInterface;

interface ConfigInterface extends CollectionInterface
{
    public function getPathDelimiter(): string;

    public function merge(array|ConfigInterface $toMerge): ConfigInterface;

    public function path(
        string $path,
        mixed $defaultValue = null,
        string $delimiter = null
    );

    public function setPathDelimiter(string $delimiter = null): ConfigInterface;
}
