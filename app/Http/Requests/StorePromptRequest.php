<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePromptRequest extends FormRequest
{
    public function authorize(): bool
    {
        // On vérifie la présence du token directement dans le contrôleur 
        // ou ici si on souhaite injecter l'utilisateur dans la requête.
        return true;
    }

    public function rules(): array
    {
        return [
            'question_text' => 'required|string|min:5|max:500',
        ];
    }

    public function messages(): array
    {
        return [
            'question_text.required' => 'Le texte de la question est obligatoire.',
            'question_text.min' => 'La question doit faire au moins 5 caractères.',
            'question_text.max' => 'La question est trop longue (500 caractères max).',
        ];
    }
}