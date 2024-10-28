<?php

namespace Mikaela\Shopright\Services;
use DateTime;
use DateTimeZone;
use Exception;
use Mikaela\Shopright\Models\Order;

class OrderLogger
{
    private static ?OrderLogger $instance = null;

    // Private constructor to prevent instantiation from outside
    private function __construct(private string $filePath)
    {
        $this->loadJsonIntoSession();
    }

    // Static method to get the singleton instance
    public static function getInstance($filePath = APP_ROOT ."/data/orders.json"): ?OrderLogger
    {
        if (self::$instance === null) {
            self::$instance = new self($filePath);
        }

        return self::$instance;
    }

    // Load the JSON data into the session if not already loaded
    private function loadJsonIntoSession(): void
    {
        $lastModified = filemtime($this->filePath);

        if (!isset($_SESSION['orderDataUpdatedAt'])) {
            $_SESSION['orderDataUpdatedAt'] = $lastModified;
        }
        // Because the session can be unreliable when you are using Postman and a browser.  We need to see if we have to
        // grab a fresh copy of the data and load that into the session.
        if (!isset($_SESSION['orderData']) || $_SESSION['orderDataUpdatedAt'] < $lastModified) {
            if (!file_exists($this->filePath)) {
                touch($this->filePath);
                $jsonContent = "{}";
            } else {
                $jsonContent = file_get_contents($this->filePath);
            }

            $data = json_decode($jsonContent, true);

            $orderData = $this->parseOrderData($data);

            if (json_last_error() === JSON_ERROR_NONE) {
                $_SESSION['orderData'] = $orderData;
            } else {
                throw new Exception("Invalid JSON format in file: " . $this->filePath);
            }

        }
    }

    /**
     * Get the loaded data from the session
     *
     * @return Order[]|null
     */
    public function getOrderData(): ?array
    {
        return $_SESSION['orderData'] ?? null;
    }

    private function parseOrderData(mixed $data): array
    {
        $orderData = [];

        if (empty($data)) {
            return $orderData;
        }

        foreach ($data as $order) {
            if (!$order['timeStamp'] instanceof DateTime) {
                $order['timeStamp'] = new DateTime($order['timeStamp']['date'], new DateTimeZone($order['timeStamp']['timezone']));
            }
            $orderData[] = new Order(
                $order['id'],
                $order['quantity'],
                $order['timeStamp'],
            );
        }

        return $orderData;
    }

    public function addOrder(int $id, int $quantity, DateTime $timeStamp): void
    {
        $_SESSION['orderData'][] = new Order($id, $quantity, $timeStamp);
    }

    // Save the session data back to the JSON file
    public function saveOrders(): void
    {

        if (isset($_SESSION['orderData'])) {

            $jsonContent = $this->getOrdersAsJSON();

            if (json_last_error() === JSON_ERROR_NONE) {
                file_put_contents($this->filePath, $jsonContent);
                $_SESSION['orderDataUpdatedAt'] = filemtime($this->filePath);
            } else {
                throw new Exception("Failed to encode JSON data.");
            }
        } else {
            throw new Exception("No data to save.");
        }
    }

    public function getOrdersAsJSON(): string
    {
        $orderJson = [];
        /** @var Order $order */
        foreach ($_SESSION['orderData'] as $order) {
            $orderJson[] = $order->toArray();
        }

        return json_encode($orderJson, JSON_PRETTY_PRINT);
    }
}