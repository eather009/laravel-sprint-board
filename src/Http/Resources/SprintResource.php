<?php

declare(strict_types=1);

namespace Eather009\LaravelSprintBoard\Http\Resources;

use Eather009\LaravelSprintBoard\Models\Sprint;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Sprint */
class SprintResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'goal' => $this->goal,
            'description' => $this->description,
            'planning_note' => $this->planning_note,
            'retrospective' => $this->retrospective,
            'dashboard_settings' => $this->dashboard_settings,
            'start_date' => optional($this->start_date)->toDateString(),
            'end_date' => optional($this->end_date)->toDateString(),
            'status' => $this->status?->value ?? $this->status,
            'leader_id' => $this->leader_id,
            'created_by' => $this->created_by,
            'members' => SprintMemberResource::collection($this->whenLoaded('members')),
            'issues' => SprintIssueResource::collection($this->whenLoaded('issues')),
            'created_at' => optional($this->created_at)->toIso8601String(),
            'updated_at' => optional($this->updated_at)->toIso8601String(),
        ];
    }
}
