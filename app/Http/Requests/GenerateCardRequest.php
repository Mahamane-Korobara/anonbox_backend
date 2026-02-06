<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GenerateCardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Géré par le middleware verify.private.token
    }

    public function rules(): array
    {
        return [
            'message_id' => 'required|integer',
        ];
    }
}
