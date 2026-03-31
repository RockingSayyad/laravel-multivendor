<?php
// app/Http/Requests/Cart/AddToCartRequest.php

namespace App\Http\Requests\Cart;

use Illuminate\Foundation\Http\FormRequest;

class AddToCartRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'product_id' => 'required|integer|exists:products,id',
            'quantity'   => 'required|integer|min:1|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.exists'  => 'The selected product does not exist.',
            'quantity.min'       => 'Quantity must be at least 1.',
            'quantity.max'       => 'You cannot add more than 100 of a single item at once.',
        ];
    }
}
