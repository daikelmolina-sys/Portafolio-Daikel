<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateOrderRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'customer_name'    => ['required', 'string', 'max:150'],
            'customer_email'   => ['nullable', 'email', 'max:150'],
            'customer_phone'   => ['nullable', 'string', 'max:20'],
            'channel'          => ['required', 'in:online,pos'],
            'delivery_type'    => ['required', 'in:pickup,delivery'],
            'delivery_address' => ['required_if:delivery_type,delivery', 'nullable', 'string'],
            'delivery_notes'   => ['nullable', 'string', 'max:500'],
            'payment_method'   => ['nullable', 'in:cash,card,yape,plin,transfer'],
            'amount_received'  => ['nullable', 'numeric', 'min:0'],
            'items'            => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity'   => ['required', 'integer', 'min:1'],
            'items.*.notes'      => ['nullable', 'string', 'max:500'],
        ];
    }
}
