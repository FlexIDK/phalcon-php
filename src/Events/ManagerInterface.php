<?php

namespace One23\PhalconPhp\Events;

interface ManagerInterface
{
    /**
     * Attach a listener to the events manager
     */
    public function attach(string $eventType, object|callable $handler): void;

    /**
     * Detach the listener from the events manager
     */
    public function detach(string $eventType, object $handler): void;

    /**
     * Removes all events from the EventsManager
     */
    public function detachAll(string $type = null): void;

    /**
     * Fires an event in the events manager causing the active listeners to be
     * notified about it
     *
     * @param object source
     * @param mixed  data
     * @return mixed
     */
    public function fire(string $eventType, object $source, mixed $data = null, bool $cancelable = true);

    /**
     * Returns all the attached listeners of a certain type
     */
    public function getListeners(string $type): array;

    /**
     * Check whether certain type of event has listeners
     */
    public function hasListeners(string $type): bool;
}
