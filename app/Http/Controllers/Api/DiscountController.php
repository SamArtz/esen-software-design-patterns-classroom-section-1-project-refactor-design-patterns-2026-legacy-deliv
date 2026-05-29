<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Discount;
use Illuminate\Http\JsonResponse;

class DiscountController extends Controller
{
    public function validateCode(string $code): JsonResponse
    {
        $discount = Discount::where('code', $code)->first();

        if (!$discount) {
            return response()->json(['valid' => false, 'error' => 'Discount not found.'], 404);
        }

        $isValid = now()->between($discount->valid_from, $discount->valid_to)
            && ($discount->max_uses === null || $discount->current_uses < $discount->max_uses);

        return response()->json([
            'valid' => $isValid,
            'discount' => $discount
        ]);
    }
}
