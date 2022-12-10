<?php

namespace One23\PhalconPhp\Domain\Payload;

use Throwable;

/**
 * This interface is used for consumers (read only)
 */
interface WriteableInterface
{
    /**
     * Sets an exception produced by the domain layer.
     *
     * @param Throwable $exception The exception thrown in the domain layer
     */
    public function setException(Throwable $exception): PayloadInterface;

    /**
     * Sets arbitrary extra values produced by the domain layer.
     *
     * @param mixed $extras Arbitrary extra values produced by the domain layer.
     */
    public function setExtras(mixed $extras): PayloadInterface;

    /**
     * Sets the input received by the domain layer.
     *
     * @param mixed $input The input received by the domain layer.
     */
    public function setInput(mixed $input): PayloadInterface;

    /**
     * Sets the messages produced by the domain layer.
     *
     * @param mixed $messages The messages produced by the domain layer.
     */
    public function setMessages(mixed $messages): PayloadInterface;

    /**
     * Sets the output produced from the domain layer.
     *
     * @param mixed $output The output produced from the domain layer.
     */
    public function setOutput(mixed $output): PayloadInterface;

    /**
     * Sets the status of this payload.
     *
     * @param mixed $status The status for this payload.
     */
    public function setStatus(mixed $status): PayloadInterface;
}
