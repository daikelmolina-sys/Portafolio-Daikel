<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ValidateCartRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'items'                  => ['required', 'array', 'min:1'],
            'items.*.product_id'     => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity'       => ['required', 'integer', 'min:1', 'max:20'],
        ];
    }

    public function messages(): array
    {
        return [
            'items.required'              => 'El carrito no puede estar vacío.',
            'items.*.product_id.exists'   => 'El producto seleccionado no existe.',
            'items.*.quantity.max'        => 'Máximo 20 unidades por producto.',
        ];
    }
}
