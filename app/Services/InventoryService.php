<?php
namespace App\Services;
use App\Models\Order;
class InventoryService
{
    public function reserveStock(Order $order): void
    {
        \App\Support\Logger::getInstance()->log("Stock reserved for order {$order->id}");
    }
    public function releaseStock(Order $order): void
    {
        \App\Support\Logger::getInstance()->log("Stock released for order {$order->id}");
    }
    public function confirmDelivery(Order $order): void
    {
        \App\Support\Logger::getInstance()->log("Delivery confirmed for order {$order->id}");
    }
}
