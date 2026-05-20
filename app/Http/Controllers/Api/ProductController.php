<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function show(Product $product): JsonResponse
    {
        return response()->json($product->load(['vendor', 'category']));
    }

    public function byVendor(Vendor $vendor): JsonResponse
    {
        $products = $vendor->products()
            ->where('available', true)
            ->with('category')
            ->get();
        return response()->json($products);
    }

    public function store(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user->role !== 'vendor' && $user->role !== 'admin') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $request->validate([
            'vendor_id'   => 'required|exists:vendors,id',
            'category_id' => 'required|exists:categories,id',
            'name'        => 'required|string|max:255',
            'price'       => 'required|numeric|min:0',
            'stock'       => 'required|integer|min:0',
        ]);

        $product = Product::create($request->only([
            'vendor_id', 'category_id', 'name', 'description',
            'price', 'image', 'available', 'stock', 'preparation_time_minutes',
        ]));

        return response()->json($product, 201);
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        $user = $request->user();
        if ($user->role !== 'admin' && $product->vendor->user_id !== $user->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $product->update($request->only([
            'name', 'description', 'price', 'image',
            'available', 'stock', 'preparation_time_minutes',
        ]));

        return response()->json($product);
    }
}
