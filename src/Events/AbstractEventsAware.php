<?php

namespace One23\PhalconPhp\Events;

/**
 * This abstract class offers access to the events manager
 */
abstract class AbstractEventsAware
{
    protected ?ManagerInterface $eventsManager = null;

    /**
     * Returns the internal event manager
     */
    public function getEventsManager(): ?ManagerInterface
    {
        return $this->eventsManager;
    }

    /**
     * Sets the events manager
     */
    public function setEventsManager(ManagerInterface $eventsManager): void
    {
        $this->eventsManager = $eventsManager;
    }

    /**
     * Helper method to fire an event
     *
     * @return mixed|bool
     */
    protected function fireManagerEvent(
        string $eventName,
        mixed $data = null,
        bool $cancellable = true
    ): mixed {
        if (!is_null($this->eventsManager)) {
            return $this
                ->eventsManager
                    ->fire($eventName, $this, $data, $cancellable);
        }

        return true;
    }
}
