<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreReportRequest extends FormRequest
{
    public function authorize(): bool { return auth()->check(); }

    public function rules(): array
    {
        return [
            'reason' => ['required', 'in:fake_listing,wrong_contact,spam,broker,unsafe,other'],
            'details' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
