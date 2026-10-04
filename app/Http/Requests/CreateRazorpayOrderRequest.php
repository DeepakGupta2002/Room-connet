<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateRazorpayOrderRequest extends FormRequest
{
    public function authorize(): bool { return auth()->check(); }
    public function rules(): array
    {
        return ['amount' => ['required', 'numeric', 'min:1', 'max:100000'], 'public_name' => ['nullable', 'string', 'max:120'], 'is_anonymous' => ['boolean'], 'show_amount' => ['boolean']];
    }
}
