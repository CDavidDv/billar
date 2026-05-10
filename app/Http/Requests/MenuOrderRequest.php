<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MenuOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => 'required|exists:products,id',
            'quantity' => 'nullable|integer|min:1|max:10',
            'notes' => 'nullable|string|max:500',
        ];
    }
}
