<?php

namespace One23\PhalconPhp\Domain\Payload;

use Throwable;

/**
 * This interface is used for consumers (read only)
 */
interface ReadableInterface
{
    /**
     * Gets the potential exception thrown in the domain layer
     */
    public function getException(): ?Throwable;

    /**
     * Gets arbitrary extra values produced by the domain layer.
     */
    public function getExtras(): mixed;

    /**
     * Gets the input received by the domain layer.
     */
    public function getInput(): mixed;

    /**
     * Gets the messages produced by the domain layer.
     */
    public function getMessages(): mixed;

    /**
     * Gets the output produced from the domain layer.
     */
    public function getOutput(): mixed;

    /**
     * Gets the status of this payload.
     */
    public function getStatus(): mixed;
}
