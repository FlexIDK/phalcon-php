<?php

namespace One23\PhalconPhp\Domain\Payload;

use Throwable;

/**
 * Holds the payload
 */
class Payload implements PayloadInterface
{
    /**
     * Exception if any
     */
    protected ?Throwable $exception = null;

    /**
     * Extra information
     */
    protected mixed $extras;

    /**
     * Input
     */
    protected mixed $input;

    /**
     * Messages
     */
    protected mixed $messages;

    /**
     * Status
     */
    protected mixed $status;

    /**
     * Output
     */
    protected mixed $output;

    /**
     * Gets the potential exception thrown in the domain layer
     *
     * @return Throwable|null
     */
    public function getException(): ?Throwable
    {
        return $this->exception;
    }

    /**
     * Extra information
     */
    public function getExtras(): mixed
    {
        return $this->extras;
    }

    /**
     * Input
     *
     * @return mixed
     */
    public function getInput(): mixed
    {
        return $this->input;
    }

    /**
     * Messages
     */
    public function getMessages(): mixed
    {
        return $this->messages;
    }

    /**
     * Status
     */
    public function getStatus(): mixed
    {
        return $this->status;
    }

    /**
     * Output
     */
    public function getOutput(): mixed
    {
        return $this->output;
    }

    /**
     * Sets an exception thrown in the domain
     */
    public function setException(Throwable $exception): PayloadInterface
    {
        $this->exception = $exception;

        return $this;
    }

    /**
     * Sets arbitrary extra domain information.
     */
    public function setExtras($extras): PayloadInterface
    {
        $this->extras = $extras;

        return $this;
    }

    /**
     * Sets the domain input.
     */
    public function setInput(mixed $input): PayloadInterface
    {
        $this->input = $input;

        return $this;
    }

    /**
     * Sets the domain messages.
     */
    public function setMessages($messages): PayloadInterface
    {
        $this->messages = $messages;

        return $this;
    }

    /**
     * Sets the domain output.
     */
    public function setOutput($output): PayloadInterface
    {
        $this->output = $output;

        return $this;
    }

    /**
     * Sets the payload status.
     */
    public function setStatus($status): PayloadInterface
    {
        $this->status = $status;

        return $this;
    }
}
