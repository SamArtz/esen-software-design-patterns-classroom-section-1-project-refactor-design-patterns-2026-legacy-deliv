<?php
namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;

class OrderViewController extends Controller
{
    public function index()
    {
        $orders = Order::with(['customer.user', 'vendor', 'payment'])
            ->orderByDesc('created_at')
            ->get();

        return view('orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        $order->load(['customer.user', 'vendor', 'courier.user', 'items', 'payment', 'discounts']);
        return view('orders.show', compact('order'));
    }
}
