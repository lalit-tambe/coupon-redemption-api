<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ApplyCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:50'],
            'cart_total' => ['required', 'numeric', 'min:0.01'],
            'user_id' => ['nullable', 'integer'],
            'order_reference' => ['nullable', 'string', 'max:100'],
        ];
    }
}
