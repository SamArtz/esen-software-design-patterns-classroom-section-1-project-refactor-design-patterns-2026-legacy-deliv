<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\Policies\OrderPolicy;
use App\Http\Requests\StoreOrderRequest;

class OrderController extends Controller
{
    use AuthorizesRequests;
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

    if ($user->role === 'admin') {
        // Los admins ven todo con paginación
        $orders = $query->with(['customer.user', 'vendor', 'courier', 'items', 'payment'])
                        ->paginate(20);
    } else {
        // Clientes, Vendors y Couriers usan el helper de relaciones
        $orders = $query->with($this->getRelationsByRole($user->role))->get();
    }

        return response()->json($orders);
    }

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $this->authorize('create', Order::class);

        try {
            $order = $request->user()->customer->placeOrder(
                $request->validated(), 
                $request->payment_method
            );

            return response()->json([
                'message' => 'Order placed successfully.',
                'order'   => $order->load(['items', 'payment', 'discounts']),
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function show(Request $request, Order $order): JsonResponse
    {
        $this->authorize('updateStatus', $order);

        return response()->json($order->load(['customer.user', 'vendor', 'courier', 'items', 'payment', 'discounts']));
    }

    public function updateStatus(Request $request, Order $order): JsonResponse
    {

        $this->authorize('updateStatus', $order);
        
        $request->validate(['status' => 'required|string']);

        try {
            $order->transitionTo($request->status);
            $order->save();            $order->notify($request->status);

            \App\Support\Logger::getInstance()->log(
                "Order {$order->id} status updated to {$request->status}"
            );

            return response()->json(['message' => 'Status updated.', 'order' => $order]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function cancel(Request $request, Order $order): JsonResponse
    {
        try {
            $order->transitionTo('cancelled');
            $order->save();
            $order->notify('cancelled');
            return response()->json(['message' => 'Order cancelled.']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    public function accept(Request $request, Order $order): JsonResponse
    {
        try {
            $order->transitionTo('accepted');
            $order->save();
            $order->notify('accepted');
            return response()->json(['message' => 'Order accepted.']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    private function getRelationsByRole(string $role): array
    {
        return match ($role) {
            'customer' => ['vendor', 'items', 'payment'],
            'vendor'   => ['customer.user', 'items', 'payment'],
            'courier'  => ['customer.user', 'vendor', 'items'],
            default    => []
        };
    }
}
