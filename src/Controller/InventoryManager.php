<?php
namespace Mikaela\Shopright\Controller;

use DateTime;
use Mikaela\Shopright\Events\LowStockEvent;
use Mikaela\Shopright\Events\NewOrderEvent;
use Mikaela\Shopright\Listeners\EventDispatcher;
use Mikaela\Shopright\Responses\HtmlResponse;
use Mikaela\Shopright\Responses\JsonResponse;
use Mikaela\Shopright\Services\NotificationService;
use Mikaela\Shopright\Services\OrderLogger;
use Mikaela\Shopright\Services\Products;

class InventoryManager
{
    const int LOW_STOCK_AMOUNT = 5;
    public function __construct(private readonly Products $products, private readonly EventDispatcher $dispatcher) {}

    public function index(array $request): string
    {
        session_destroy();

        return "Destroyed";
    }

    /**
     * This is the log HTML view method
     * http://localhost:8080/InventoryManager/ViewLog
     *
     * @param array $request
     * @return HtmlResponse
     */
    public function viewLog(array $request): HtmlResponse
    {
        $order = OrderLogger::getInstance();
        $orderData = $order->getOrderData();

        $notifications = NotificationService::getInstance();

        $orderTable = "";
        foreach ($orderData as $order) {
            $orderTable .= "<tr><td>{$order->getId()}</td><td>{$order->getQuantity()}</td><td>{$order->getTimeStamp()->format('d/m/Y H:i:s')}</td></tr>";
        }

        $notificationTable = "";
        foreach ($notifications->getNotifications() as $notification) {
            $notificationTable .= "<tr><td>{$notification}</td></tr>";
        }

        return new HtmlResponse(200, ['view'=>'log','viewData' => ['orderData' => $orderTable, 'notificationData' => $notificationTable]]);
    }

    /**
     * This is the POST request for processing order.
     * POST http://127.0.0.1:8080/InventoryManager/ProcessOrder
     *
     * @param array $request
     * @return JsonResponse
     * @throws \Exception
     */
    public function processOrder(array $request): JsonResponse
    {
        if (empty($request['body']['quantity']) || !is_int($request['body']['quantity'])) {
            throw new \Exception('A numeric quantity is required.');
        }

        if (empty($request['body']['id'])) {
            throw new \Exception('product ID is required.');
        }

        $product = $this->products->getProduct($request['body']['id']);
        if ($product === null) {
            throw new \Exception("Unable to find a product with id {$request['body']['id']}.");
        }
        if ($request['body']['quantity'] > $product->getStock()) {
            throw new \Exception('unable to fulfill required quantity. Stock available for ' . $product->getName() . ' is ' . $product->getStock());
        }

        $product->subtractStock($request['body']['quantity']);
        $event = new NewOrderEvent($product->getId(), $request['body']['quantity'], new DateTime());
        $this->dispatcher->dispatch($event);

        if ($product->getStock() < self::LOW_STOCK_AMOUNT) {
            $event = new LowStockEvent($product->getId(), $product->getName(), $product->getStock());
            $this->dispatcher->dispatch($event);
        }

        $this->products->saveProducts();

        return new JsonResponse(200, ['message' => 'Stock update was successful']);
    }
}