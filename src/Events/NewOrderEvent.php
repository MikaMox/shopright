<?php

namespace Mikaela\Shopright\Events;

use DateTime;

class NewOrderEvent
{
    public function __construct(public int $id, public string $quantity, public DateTime $timeStamp) {}
}