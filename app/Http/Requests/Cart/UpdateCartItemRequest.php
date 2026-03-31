<?php
// app/Http/Requests/Cart/UpdateCartItemRequest.php

namespace App\Http\Requests\Cart;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCartItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'quantity' => 'required|integer|min:0|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'quantity.min' => 'Quantity cannot be negative. Use 0 to remove the item.',
        ];
    }
}
