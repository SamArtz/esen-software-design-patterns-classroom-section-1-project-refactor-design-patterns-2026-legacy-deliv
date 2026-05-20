<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CustomerController extends Controller
{
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user->role !== 'customer') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        return response()->json($user->customer()->with('user')->first());
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user->role !== 'customer') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'address'   => 'nullable|string',
            'city'      => 'nullable|string',
            'latitude'  => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
        ]);

        $customer = $user->customer;
        $customer->update($request->only(['address', 'city', 'latitude', 'longitude', 'preferred_payment_method']));

        return response()->json($customer);
    }
}
