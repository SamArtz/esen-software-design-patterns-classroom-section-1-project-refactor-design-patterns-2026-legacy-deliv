<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Policies;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;


class CustomerController extends Controller
{   
    use AuthorizesRequests;
    public function profile(Request $request): JsonResponse
    {
        $customer = $request->user()->customer;

        // Verificamos con la Policy
        $this->authorize('viewOrUpdate', $customer);

        return response()->json($user->customer()->with('user')->first());
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $customer = $request->user()->customer;

        // Verificamos con la Policy
        $this->authorize('viewOrUpdate', $customer);

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
