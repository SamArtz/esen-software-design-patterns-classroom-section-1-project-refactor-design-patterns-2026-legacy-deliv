<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OrderPolicy
{
    public function view(User $user, Order $order): bool
    {
        if ($user->role === 'admin') return true;
        
        return match ($user->role) {
            'customer' => $order->customer_id === $user->customer?->id,
            'vendor'   => $order->vendor_id === $user->vendor?->id,
            'courier'  => $order->courier_id === $user->courier?->id,
            default    => false,
        };
    }

    public function create(User $user): bool
    {
        return strtolower(trim($user->role)) === 'customer';
    }

    public function updateStatus(User $user, Order $order): bool
    {
        if ($user->role === 'admin') return true;
        // Solo el vendor o courier asignado pueden mover estados
        return ($user->role === 'vendor' && $order->vendor_id === $user->vendor?->id)
            || ($user->role === 'courier' && $order->courier_id === $user->courier?->id);
    }
}
