<?php

namespace One23\PhalconPhp\Support\Collection;

interface CollectionInterface
{
    public function __get(string $element);

    public function __isset(string $element): bool;

    public function __set(string $element, mixed $value): void;

    public function __unset(string $element): void;

    public function clear(): void;

    public function get(string $element, mixed $defaultValue = null, string $cast = null);

    public function getKeys(bool $insensitive = true): array;

    public function getValues(): array;

    public function has(string $element): bool;

    public function init(array $data = []): void;

    public function remove(string $element): void;

    public function set(string $element, mixed $value): void;

    public function toArray(): array;

    public function toJson(int $options = 79): string;
}
