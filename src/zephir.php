<?php

namespace One23\PhalconPhp;

function create_instance(mixed $class) {
    if (!isset($class) || !is_string($class)) { // YES
        throw new \Exception("Invalid class name");
    }

    if (!class_exists($class)) { // YES
        throw new \Exception("Class '{$class}' does not exist");
    }

    return new $class;
}
