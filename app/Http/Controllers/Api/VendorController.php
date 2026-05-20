<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class VendorController extends Controller
{
    public function index(): JsonResponse
    {
        $vendors = Vendor::where('status', 'active')
            ->withCount('products')
            ->get();
        return response()->json($vendors);
    }

    public function show(Vendor $vendor): JsonResponse
    {
        return response()->json($vendor->load(['categories', 'products']));
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'business_name'   => 'required|string|max:255',
            'address'         => 'required|string',
            'city'            => 'required|string',
            'commission_rate' => 'nullable|numeric|min:0|max:100',
        ]);

        $user = $request->user();
        if ($user->role !== 'vendor' && $user->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $vendor = Vendor::create(array_merge(
            $request->only(['business_name', 'description', 'address', 'city', 'commission_rate']),
            ['user_id' => $user->id, 'status' => 'active']
        ));

        return response()->json($vendor, 201);
    }

    public function update(Request $request, Vendor $vendor): JsonResponse
    {
        $user = $request->user();
        if ($user->role !== 'admin' && $vendor->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $vendor->update($request->only([
            'business_name', 'description', 'address', 'city',
            'opening_hours', 'commission_rate', 'status',
        ]));

        return response()->json($vendor);
    }

    public function orders(Request $request, Vendor $vendor): JsonResponse
    {
        $user = $request->user();
        if ($user->role !== 'admin' && $vendor->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json($vendor->orders()->with(['customer.user', 'items'])->get());
    }

    public function bundles(Vendor $vendor): JsonResponse
    {
        return response()->json($vendor->productBundles()->where('available', true)->get());
    }

    public function categories(Vendor $vendor): JsonResponse
    {
        return response()->json($vendor->categories()->with('children')->whereNull('parent_category_id')->get());
    }
}
