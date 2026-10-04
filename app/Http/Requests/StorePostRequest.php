<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePostRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check();
    }

    public function rules(): array
    {
        return [
            'listed_by_role' => ['required', 'in:tenant,owner'],
            'title' => ['required', 'string', 'max:180'],
            'description' => ['required', 'string', 'min:20', 'max:5000'],
            'rent_amount' => ['required', 'numeric', 'min:1', 'max:99999999'],
            'security_deposit' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'maintenance_charge' => ['nullable', 'numeric', 'min:0', 'max:99999999'],
            'room_type' => ['required', 'in:private_room,shared_room,shared_flat,studio,1bhk,2bhk,3bhk'],
            'available_from' => ['required', 'date', 'after_or_equal:today'],
            'leaving_date' => ['nullable', 'date', 'after_or_equal:available_from'],
            'city' => ['required', 'string', 'max:100'],
            'area' => ['required', 'string', 'max:120'],
            'locality' => ['nullable', 'string', 'max:120'],
            'pincode' => ['nullable', 'string', 'max:12'],
            'approximate_address' => ['nullable', 'string', 'max:1000'],
            'owner_name' => ['required', 'string', 'max:120'],
            'owner_phone' => ['required', 'string', 'regex:/^[0-9+() -]{8,20}$/'],
        ];
    }

    public function messages(): array
    {
        return [
            'listed_by_role.in' => 'Listing sirf tenant ya owner ke naam se create ho sakti hai.',
            'owner_phone.regex' => 'Valid owner contact number enter karein.',
            'leaving_date.after_or_equal' => 'Leaving date available date ke baad honi chahiye.',
        ];
    }
}
