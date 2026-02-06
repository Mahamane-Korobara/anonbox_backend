<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Envoi anonyme ouvert à tous
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'prompt_id' => 'nullable|exists:prompts,id',
            'anonymous_content' => 'required|string|min:1|max:1000',
        ];
    }
}
