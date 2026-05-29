<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
    // Solo permitimos que los clientes creen órdenes
        return true;
    }
    public function rules(): array
    {
    return [
        'vendor_id'        => 'required|exists:vendors,id',
        'items'            => 'required|array|min:1',
        'items.*.type'     => 'required|in:product,bundle',
        'items.*.id'       => 'required|integer',
        'items.*.quantity' => 'required|integer|min:1',
        'payment_method'   => 'required|in:card,cash,wallet,transfer',
        'discount_code'    => 'nullable|string',
        'notes'            => 'nullable|string|max:500',
    ];
    }
}
