<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Autoriser tout le monde à créer un profil
    }

    public function rules(): array
    {
        return [
            'display_name' => 'required|string|min:2|max:100',
        ];
    }

    public function messages(): array
    {
        return [
            'display_name.required' => 'Le nom d\'affichage est obligatoire.',
            'display_name.min' => 'Le nom doit contenir au moins 2 caractères.',
        ];
    }
}
