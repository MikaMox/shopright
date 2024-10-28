<?php

namespace Mikaela\Shopright\Models;
class Product
{
    function __construct(private int $id, private string $name, private int $stock, private float $price) {}

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

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getStock(): int
    {
        return $this->stock;
    }

    public function setStock(int $stock): void
    {
        $this->stock = $stock;
    }

    public function subtractStock(int $stock): void
    {
        if ($stock <= $this->stock) {
            $this->stock = $this->stock - $stock;
        } else {
            throw new \Exception("Stock out of range");
        }
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $price): void
    {
        $this->price = $price;
    }
}