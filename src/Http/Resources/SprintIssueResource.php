<?php

declare(strict_types=1);

namespace Eather009\LaravelSprintBoard\Http\Resources;

use Eather009\LaravelSprintBoard\Enums\IssueCompletionStatus;
use Eather009\LaravelSprintBoard\Models\SprintIssue;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin SprintIssue */
class SprintIssueResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $status = $this->completion_status;

        return [
            'id' => $this->id,
            'sprint_id' => $this->sprint_id,
            'tracker' => $this->tracker,
            'external_project_id' => $this->external_project_id,
            'external_issue_id' => $this->external_issue_id,
            'added_by' => $this->added_by,
            'added_at' => $this->added_at?->toIso8601String(),
            'priority_id' => $this->priority_id,
            'completion_status' => $status instanceof IssueCompletionStatus ? $status->value : $status,
            'completion_note' => $this->completion_note,
            'completion_updated_by' => $this->completion_updated_by,
            'completion_updated_at' => $this->completion_updated_at?->toIso8601String(),
        ];
    }
}
