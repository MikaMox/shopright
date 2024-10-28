<?php

namespace Mikaela\Shopright\Listeners;

class EventDispatcher
{
    protected array $listeners = [];

    public function listen($eventName, $listener): void
    {
        $this->listeners[$eventName][] = $listener;
    }

    public function dispatch($event): void
    {
        $eventName = get_class($event);

        if (isset($this->listeners[$eventName])) {
            foreach ($this->listeners[$eventName] as $listener) {
                $listener->handle($event);
            }
        }
    }
}