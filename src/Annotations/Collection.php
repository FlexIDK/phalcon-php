<?php

namespace One23\PhalconPhp\Annotations;

use Countable;
use Iterator;

/**
 * Represents a collection of annotations. This class allows to traverse a group
 * of annotations easily
 */
class Collection implements Iterator, Countable
{
    protected array $annotations;

    protected int $position = 0;

    public function __construct(array $reflectionData = [])
    {
        $annotations = [];

        foreach ($reflectionData as $annotationData) {
            $annotations[] = new Annotation($annotationData);
        }

        $this->annotations = $annotations;
    }

    /**
     * Returns the number of annotations in the collection
     */
    public function count(): int
    {
        return count($this->annotations);
    }

    /**
     * Returns the current annotation in the iterator
     */
    public function current(): mixed
    {
        if (!isset($this->annotations[$this->position])) {
            return false;
        }

        return $this->annotations[$this->position];
    }

    /**
     * Returns the first annotation that match a name
     */
    public function get(string $name): Annotation
    {
        /** @var Annotation $annotation */
        foreach ($this->annotations as $annotation) {
            if ($annotation->getName() === $name) {
                return $annotation;
            }
        }

        throw new Exception(
            "Collection doesn't have an annotation called '{$name}'"
        );
    }

    /**
     * Returns all the annotations that match a name
     *
     * @return Annotation[]
     */
    public function getAll(string $name): array
    {
        $found = [];

        /** @var Annotation $annotation */
        foreach ($this->annotations as $annotation) {
            if ($annotation->getName() === $name) {
                $found[] = $annotation;
            }
        }

        return $found;
    }

    /**
     * Returns the internal annotations as an array
     *
     * @return Annotation[]
     */
    public function getAnnotations(): array
    {
        return $this->annotations;
    }

    /**
     * Check if an annotation exists in a collection
     */
    public function has(string $name)
    {
        $annotations = $this->annotations;

        /** @var Annotation $annotation */
        foreach ($this->annotations as $annotation) {
            if ($annotation->getName() === $name) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the current position/key in the iterator
     */
    public function key(): int
    {
        return $this->position;
    }

    /**
     * Moves the internal iteration pointer to the next position
     */
    public function next(): void
    {
        $this->position++;
    }

    /**
     * Rewinds the internal iterator
     */
    public function rewind(): void
    {
        $this->position = 0;
    }

    /**
     * Check if the current annotation in the iterator is valid
     */
    public function valid(): bool
    {
        return isset($this->annotations[$this->position]);
    }
}
