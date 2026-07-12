<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],

            'billing.country' => ['required', 'string', 'max:255'],
            'billing.billing_address' => ['required', 'string', 'max:255'],
            'billing.city' => ['required', 'string', 'max:255'],
            'billing.state' => ['required', 'string', 'max:255'],
            'billing.zipcode' => ['required', 'string', 'max:255'],
            'billing.phone' => ['required', 'string', 'max:255'],
            'billing.order_notes' => ['nullable', 'string'],
        ];
    }
}
