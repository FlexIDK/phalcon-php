<?php

namespace One23\PhalconPhp\Annotations;

interface ReaderInterface
{
    /**
     * Reads annotations from the class docblocks, its constants, properties and methods
     */
    public function parse(string $className): array;

    /**
     * Parses a raw docblock returning the annotations found
     */
    public static function parseDocBlock(string $docBlock, $file = null, $line = null): array;
}
