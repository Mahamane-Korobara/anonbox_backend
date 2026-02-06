<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'display_name' => $this->display_name,
            'handle' => $this->handle,
            'public_url' => $this->public_url,
            // On n'inclut les stats que si elles sont chargées ou pertinentes
            'stats' => [
                'messages_received' => $this->total_messages_received ?? 0,
                'responses_posted' => $this->total_responses_posted ?? 0,
            ],
            // On peut ajouter des liens HATEOAS pour faciliter le dev front-end
            'links' => [
                'inbox' => $this->inbox_url,
            ]
        ];
    }
}
