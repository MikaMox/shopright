<?php

namespace Mikaela\Shopright\Models;
use DateTime;

class Order
{
    function __construct(private int $id, private int $quantity, private DateTime $timeStamp) {}

    public function toArray(): array
    {
        return get_object_vars($this);
    }

    function getProduct(): string
    {
        return json_encode($this->toArray());
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getTimeStamp(): DateTime
    {
        return $this->timeStamp;
    }

    public function setTimeStamp(DateTime $timeStamp): void
    {
        $this->timeStamp = $timeStamp;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): void
    {
        $this->quantity = $quantity;
    }
}