<?php

namespace Mikaela\Shopright\Services;
use Exception;
use Mikaela\Shopright\Models\Product;

class NotificationService
{
    private static ?NotificationService $instance = null;

    // Private constructor to prevent instantiation from outside
    private function __construct(private string $filePath)
    {
        $this->loadJsonIntoSession();
    }

    // Static method to get the singleton instance
    public static function getInstance($filePath = APP_ROOT ."/data/logs.json"): ?NotificationService
    {
        if (self::$instance === null) {
            self::$instance = new self($filePath);
        }

        return self::$instance;
    }

    // Load the JSON data into the session if not already loaded
    private function loadJsonIntoSession(): void
    {
        if (!file_exists($this->filePath)) {
            touch($this->filePath);
        }

        $lastModified = filemtime($this->filePath);
        if (!isset($_SESSION['notificationsUpdatedAt'])) {
            $_SESSION['notificationsUpdatedAt'] = $lastModified;
        }

        if (!isset($_SESSION['notifications']) || $_SESSION['notificationsUpdatedAt'] < $lastModified) {
            $jsonContent = file_get_contents($this->filePath);
            if (empty($jsonContent)) {
                $jsonContent = "{}";
            }
            $notifications = json_decode($jsonContent, true);

            if (json_last_error() === JSON_ERROR_NONE) {
                $_SESSION['notifications'] = $notifications;
            } else {
                throw new Exception("Invalid JSON format in file: " . $this->filePath);
            }

        }

    }

    /**
     * Get the loaded data from the session
     *
     * @return Product[]|null
     */
    public function getNotifications(): ?array
    {
        return $_SESSION['notifications'] ?? null;
    }

    public function setNotification(string $notification): void
    {
        if (!isset($_SESSION['notifications'])) {
            $_SESSION['notifications'] = [];
        }

        $_SESSION['notifications'][] = $notification;
    }

    // Save the session data back to the JSON file
    public function saveNotifications(): void
    {
        if (isset($_SESSION['notifications'])) {

            $jsonContent = $this->getNotificationsAsJSON();

            if (json_last_error() === JSON_ERROR_NONE) {
                file_put_contents($this->filePath, $jsonContent);
                $_SESSION['notificationsUpdatedAt'] = filemtime($this->filePath);
            } else {
                throw new Exception("Failed to encode JSON data.");
            }
        } else {
            throw new Exception("No data to save.");
        }
    }

    public function getNotificationsAsJSON(): string
    {
        return json_encode($_SESSION['notifications'], JSON_PRETTY_PRINT);
    }
}