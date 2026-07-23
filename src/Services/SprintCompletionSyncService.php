<?php

declare(strict_types=1);

namespace Eather009\LaravelSprintBoard\Services;

use Eather009\LaravelSprintBoard\Contracts\IssueTracker;
use Eather009\LaravelSprintBoard\Contracts\SprintUser;
use Eather009\LaravelSprintBoard\Enums\IssueCompletionStatus;
use Eather009\LaravelSprintBoard\Models\Sprint;
use Eather009\LaravelSprintBoard\Models\SprintIssue;

class SprintCompletionSyncService
{
    public function __construct(
        protected IssueTracker $tracker,
        protected SprintHydrateService $hydrateService,
        protected SprintCompletionService $completionService,
    ) {}

    /**
     * Refresh hydrate cache and mark closed remote issues completed.
     *
     * @return array{hydrated: int, completed: int}
     */
    public function refreshAndSync(SprintUser $actor, Sprint $sprint): array
    {
        $hydrated = $this->hydrateService->hydrate($actor, $sprint, bust: true);
        $completed = 0;

        $uniqueIssues = $sprint->issues()
            ->get()
            ->unique(fn (SprintIssue $issue): string => $issue->tracker.'|'.$issue->external_project_id.'|'.$issue->external_issue_id);

        foreach ($uniqueIssues as $issue) {
            $payload = $hydrated[(string) $issue->external_issue_id] ?? null;
            if ($payload === null) {
                continue;
            }

            if (! $this->tracker->isClosed($payload)) {
                continue;
            }

            if ($issue->completion_status === IssueCompletionStatus::Completed) {
                continue;
            }

            $this->completionService->update(
                $actor,
                $sprint,
                $issue,
                IssueCompletionStatus::Completed,
                'Synced from tracker (closed).'
            );
            $completed++;
        }

        return [
            'hydrated' => count($hydrated),
            'completed' => $completed,
        ];
    }
}
