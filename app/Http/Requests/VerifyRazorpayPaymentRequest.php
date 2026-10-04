<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyRazorpayPaymentRequest extends FormRequest
{
    public function authorize(): bool { return auth()->check(); }
    public function rules(): array
    {
        return ['donation_id' => ['required', 'integer', 'exists:donations,id'], 'razorpay_payment_id' => ['required', 'string', 'max:150'], 'razorpay_order_id' => ['required', 'string', 'max:150'], 'razorpay_signature' => ['required', 'string', 'size:64']];
    }
}
