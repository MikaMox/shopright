<?php

namespace Mikaela\Shopright\Events;

class LowStockEvent
{
    public function __construct(public int $id, public string $productName, public int $quantity) {}
}