<?php

namespace One23\PhalconPhp\Domain\Payload;

class PayloadFactory
{
    /**
     * Instantiate a new object
     */
    public function newInstance(): PayloadInterface
    {
        return new Payload();
    }
}
