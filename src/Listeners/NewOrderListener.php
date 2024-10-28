<?php

namespace Mikaela\Shopright\Listeners;

use Mikaela\Shopright\Events\NewOrderEvent;
use Mikaela\Shopright\Services\OrderLogger;

class NewOrderListener
{
    public function handle(NewOrderEvent $event): void
    {
        $orderLogger = OrderLogger::getInstance();

        $orderLogger->addOrder($event->id, $event->quantity, $event->timeStamp);
        $orderLogger->saveOrders();
    }
}