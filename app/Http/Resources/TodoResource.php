<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Enums\TodoStatus;

/**
 * @mixin \App\Models\Todo
 */
class TodoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status instanceof TodoStatus ? $this->status->value : $this->status,
            'priority' => $this->priority,
            'due_date' => optional($this->due_date)->toDateString(),
            'completed_at' => optional($this->completed_at)?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
