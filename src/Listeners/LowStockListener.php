<?php

namespace Mikaela\Shopright\Listeners;

use Mikaela\Shopright\Events\LowStockEvent;
use Mikaela\Shopright\Services\NotificationService;

class LowStockListener
{
    public function handle(LowStockEvent $event): void
    {
        // Create the notification message
        $message = "Low stock alert for {$event->id}: {$event->productName}: only {$event->quantity} left.";
        $notifications = NotificationService::getInstance();
        $notifications->setNotification($message);
        $notifications->saveNotifications();
    }
}