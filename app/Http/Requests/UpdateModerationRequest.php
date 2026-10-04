<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateModerationRequest extends FormRequest
{
    public function authorize(): bool { return auth()->check(); }

    public function rules(): array
    {
        return [
            'status' => ['required', 'in:reviewing,resolved,dismissed'],
            'moderation_action' => ['required', 'in:none,hide,restore'],
            'moderation_note' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
