<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ValidateCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code'              => ['required', 'string'],
            'subtotal'          => ['required', 'integer', 'min:0'],
            'items'             => ['required', 'array', 'min:1'],
            'items.*.product_id'=> ['required', 'integer'],
            'items.*.quantity'  => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required'     => 'El código de cupón es obligatorio.',
            'subtotal.required' => 'El subtotal es obligatorio.',
            'items.required'    => 'Debes enviar al menos un producto.',
        ];
    }
}
