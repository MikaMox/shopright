<?php
require __DIR__ . '/vendor/autoload.php';
use Mikaela\Shopright\Controller\FrontController;
use Mikaela\Shopright\Events\LowStockEvent;
use Mikaela\Shopright\Events\NewOrderEvent;
use Mikaela\Shopright\Listeners\EventDispatcher;
use Mikaela\Shopright\Listeners\LowStockListener;
use Mikaela\Shopright\Listeners\NewOrderListener;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

const APP_ROOT = __DIR__;

// register event and listener
$dispatcher = new EventDispatcher();
$dispatcher->listen(LowStockEvent::class, new LowStockListener());
$dispatcher->listen(NewOrderEvent::class, new NewOrderListener());

$frontController = new FrontController(
    $_SERVER['REQUEST_URI'],
    [
        "request" => $_REQUEST,
        "body" => json_decode(file_get_contents('php://input'), true)
    ],
    $dispatcher
);

echo $frontController->response();