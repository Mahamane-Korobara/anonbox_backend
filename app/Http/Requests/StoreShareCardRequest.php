<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreShareCardRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Géré par verify.private.token
    }

    public function rules(): array
    {
        return [
            'image' => 'required|file|image|max:8192',
            'cardText' => 'nullable|string|max:500',
            'shareText' => 'required|string|max:1500',
            'targetUrl' => 'required|url|max:1000',
            'isMessage' => 'nullable',
        ];
    }

    public function messages(): array
    {
        return [
            'image.required' => 'Image requise.',
            'image.image' => 'Le fichier doit être une image valide.',
            'shareText.required' => 'Le texte de partage est requis.',
            'targetUrl.required' => 'Le lien cible est requis.',
            'targetUrl.url' => 'Le lien cible est invalide.',
        ];
    }
}
