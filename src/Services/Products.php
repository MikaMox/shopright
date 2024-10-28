<?php

namespace Mikaela\Shopright\Services;
use Exception;
use Mikaela\Shopright\Models\Product;

class Products
{
    private static ?Products $instance = null;

    // Private constructor to prevent instantiation from outside
    private function __construct(private readonly string $filePath)
    {
        $this->loadJsonIntoSession();
    }

    // Static method to get the singleton instance
    public static function getInstance($filePath = APP_ROOT ."/data/products.json"): ?Products
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
        if (!isset($_SESSION['productDataUpdatedAt'])) {
            $_SESSION['productDataUpdatedAt'] = $lastModified;
        }
        if (!isset($_SESSION['productData']) || $_SESSION['productDataUpdatedAt'] < $lastModified) {
            if (file_exists($this->filePath)) {
                $jsonContent = file_get_contents($this->filePath);
                $data = json_decode($jsonContent, true);

                $productData = $this->parseProductData($data);

                if (json_last_error() === JSON_ERROR_NONE) {
                    $_SESSION['productData'] = $productData;
                } else {
                    throw new Exception("Invalid JSON format in file: " . $this->filePath);
                }
            } else {
                throw new Exception("File not found: " . $this->filePath);
            }
        }

    }

    /**
     * Get the loaded data from the session
     *
     * @return Product[]|null
     */
    public function getProductData(): ?array
    {
        return $_SESSION['productData'] ?? null;
    }

    public function getProduct(int $id): ?Product
    {
        return $_SESSION['productData'][$id] ?? null;
    }

    private function parseProductData(mixed $data): array
    {
        $productData = [];

        foreach ($data as $product) {
            $productData[$product['id']] = new Product(
                $product['id'],
                $product['name'],
                $product['stock'],
                $product['price']
            );
        }

        return $productData;
    }

    // Save the session data back to the JSON file
    public function saveProducts(): void
    {
        if (isset($_SESSION['productData'])) {

            $jsonContent = $this->getProductsAsJSON();

            if (json_last_error() === JSON_ERROR_NONE) {
                file_put_contents($this->filePath, $jsonContent);
                $_SESSION['productDataUpdatedAt'] = filemtime($this->filePath);
            } else {
                throw new Exception("Failed to encode JSON data.");
            }
        } else {
            throw new Exception("No data to save.");
        }
    }

    public function getProductsAsJSON(): string
    {
        $productJson = [];
        /** @var Product $product */
        foreach ($_SESSION['productData'] as $product) {
            $productJson[] = $product->toArray();
        }

        return json_encode($productJson, JSON_PRETTY_PRINT);
    }
}