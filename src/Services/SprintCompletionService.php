<?php

declare(strict_types=1);

namespace Eather009\LaravelSprintBoard\Services;

use Eather009\LaravelSprintBoard\Contracts\SprintAuthorizer;
use Eather009\LaravelSprintBoard\Contracts\SprintUser;
use Eather009\LaravelSprintBoard\Enums\IssueCompletionStatus;
use Eather009\LaravelSprintBoard\Exceptions\SprintAuthorizationException;
use Eather009\LaravelSprintBoard\Exceptions\SprintValidationException;
use Eather009\LaravelSprintBoard\Models\Sprint;
use Eather009\LaravelSprintBoard\Models\SprintIssue;
use Illuminate\Support\Facades\DB;

class SprintCompletionService
{
    public function __construct(
        protected SprintAuthorizer $authorizer,
    ) {}

    public function update(
        SprintUser $actor,
        Sprint $sprint,
        SprintIssue $issue,
        IssueCompletionStatus $status,
        ?string $note = null,
    ): SprintIssue {
        if ((int) $issue->sprint_id !== (int) $sprint->id) {
            throw new SprintValidationException('Issue does not belong to this sprint.');
        }

        if (! $this->authorizer->canUpdateCompletion($actor, $sprint, $issue)) {
            throw new SprintAuthorizationException('You are not allowed to update completion for this issue.');
        }

        return DB::transaction(function () use ($actor, $sprint, $issue, $status, $note): SprintIssue {
            $now = now();

            SprintIssue::query()
                ->where('sprint_id', $sprint->id)
                ->where('tracker', $issue->tracker)
                ->where('external_project_id', $issue->external_project_id)
                ->where('external_issue_id', $issue->external_issue_id)
                ->update([
                    'completion_status' => $status->value,
                    'completion_note' => $note,
                    'completion_updated_by' => $actor->id(),
                    'completion_updated_at' => $now,
                    'updated_at' => $now,
                ]);

            return $issue->fresh();
        });
    }
}
