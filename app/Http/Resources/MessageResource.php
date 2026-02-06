<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'anonymous_content' => $this->anonymous_content,
            'response_content' => $this->response_content,
            'status' => $this->status,
            'prompt' => new PromptResource($this->whenLoaded('prompt')),
            'meta' => [
                'has_response' => $this->has_response,
                'is_shared' => $this->is_shared,
                'created_at' => $this->created_at->toIso8601String(),
                'read_at' => $this->read_at?->toIso8601String(),
                'responded_at' => $this->responded_at?->toIso8601String(),
            ]
        ];
    }
}
