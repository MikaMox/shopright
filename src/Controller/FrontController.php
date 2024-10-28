<?php

namespace Mikaela\Shopright\Controller;

use Exception;
use Mikaela\Shopright\Listeners\EventDispatcher;
use Mikaela\Shopright\Services\NotificationService;
use Mikaela\Shopright\Services\Products;
use ReflectionClass;

class FrontController
{
    // Routing is defined here with the controller and methods that are available (index is assumed to be available)
    private array $routing = [
        'InventoryManager' => [
            'viewLog',
            'processOrder'
        ]
    ];

    private string $controllerNamespace = "Mikaela\Shopright\Controller\\";
    private string $className;
    private string $classMethod;


    public function __construct(private string $routingPath, private array $request, private EventDispatcher $dispatcher)
    {
        $routeStubs = array_filter(explode("/", preg_replace('/[^a-zA-Z\/]/', '', $this->routingPath)));
        $className = current($routeStubs);
        if (isset($this->routing[$className])) {
            $this->className = $className;
            $classMethod = lcfirst(next($routeStubs));
            $this->classMethod = in_array($classMethod, $this->routing[$className]) ? $classMethod : "index";
        } else {
            throw new Exception("Route " . $className . " does not exist");
        }
    }

    public function response(): string
    {
        try {
            // Use reflection to load the class
            $reflector = new ReflectionClass($this->controllerNamespace . $this->className);

            // Check if the class has the specified method
            if (!$reflector->hasMethod($this->classMethod)) {
                throw new Exception("Method $this->classMethod does not exist in class $this->className.");
            }

            // Create an instance of the class
            // I should do some reflection here to see what needs injecting into the class.  But for this
            // I am going to assume that the products class and event dispatcher are always injected into the controller

            $instance = $reflector->newInstance(Products::getInstance(), $this->dispatcher);

            // Get the method and invoke it with the provided parameters
            $method = $reflector->getMethod($this->classMethod);
            return $method->invokeArgs($instance, [$this->request]);

        } catch (ReflectionException $e) {
            return $this->handleError($e, 500);
        } catch (Exception $e) {
            return $this->handleError($e);
        }
    }

    private function handleError($exception,$errorCode = 400) {
        http_response_code($errorCode);
        $notifications = NotificationService::getInstance();
        $notifications->setNotification("error : {$errorCode} - {$exception->getMessage()}");
        $notifications->saveNotifications();

        // Should have probably created specific jsonException classes to enable proper content types
        if (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') === 0) {
            header('Content-Type: application/json');
            return json_encode(['error' => $exception->getMessage()]);
        } else {
            echo $exception->getMessage();
        }
    }
}