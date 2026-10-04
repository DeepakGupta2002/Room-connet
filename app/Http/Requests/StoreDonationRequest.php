<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDonationRequest extends FormRequest
{
    public function authorize(): bool { return auth()->check(); }

    public function rules(): array
    {
        return [
            'amount' => ['required', 'numeric', 'min:1', 'max:100000'],
            'public_name' => ['nullable', 'string', 'max:120'],
            'is_anonymous' => ['boolean'],
            'show_amount' => ['boolean'],
            'payment_method' => ['required', 'in:razorpay,qr'],
            'payment_reference' => ['required_if:payment_method,qr', 'nullable', 'string', 'max:150', 'unique:donations,payment_reference'],
            'proof' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:5120'],
        ];
    }
}
