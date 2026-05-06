<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApiFetchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'source' => $this->source,
            'external_id' => $this->external_id,
            'payload' => $this->payload,
            'fetched_at' => optional($this->fetched_at)->toISOString(),
            'created_at' => optional($this->created_at)->toISOString(),
        ];
    }
}
