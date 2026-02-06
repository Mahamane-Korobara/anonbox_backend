<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RespondMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // La vérification du token se fait dans le contrôleur
    }

    public function rules(): array
    {
        return [
            'response_content' => 'required|string|min:1|max:1000',
        ];
    }
}
