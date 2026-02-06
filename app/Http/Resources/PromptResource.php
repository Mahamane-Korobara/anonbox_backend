<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PromptResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'question_text' => $this->question_text,
            'share_url' => $this->share_url,
            'stats' => [
                'messages_received' => $this->messages_received ?? 0,
                'times_shared' => $this->times_shared ?? 0,
            ],
            // On inclut l'utilisateur seulement s'il est chargé (Eager Loading)
            'user' => new UserResource($this->whenLoaded('user')),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
