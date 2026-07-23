<?php

declare(strict_types=1);

namespace Eather009\LaravelSprintBoard\Services;

use Eather009\LaravelSprintBoard\Contracts\IssueTracker;
use Eather009\LaravelSprintBoard\Contracts\SprintAuthorizer;
use Eather009\LaravelSprintBoard\Contracts\SprintUser;
use Eather009\LaravelSprintBoard\Enums\IssueCompletionStatus;
use Eather009\LaravelSprintBoard\Exceptions\SprintAuthorizationException;
use Eather009\LaravelSprintBoard\Exceptions\SprintValidationException;
use Eather009\LaravelSprintBoard\Models\Sprint;
use Eather009\LaravelSprintBoard\Models\SprintIssue;
use Eather009\LaravelSprintBoard\Trackers\NullIssueTracker;

class SprintIssueService
{
    public function __construct(
        protected SprintAuthorizer $authorizer,
        protected IssueTracker $tracker,
    ) {}

    /**
     * @param  array{external_project_id: string, external_issue_id: string, tracker?: string, priority_id?: int|null}  $payload
     */
    public function link(SprintUser $actor, Sprint $sprint, array $payload): SprintIssue
    {
        if (! $this->authorizer->canView($actor, $sprint)) {
            throw new SprintAuthorizationException('You are not allowed to link issues to this sprint.');
        }

        $tracker = $payload['tracker'] ?? (string) config('sprint.tracker_default', 'backlog');
        if ($tracker === 'null' || $tracker === '') {
            $tracker = 'backlog';
        }

        $projectId = (string) ($payload['external_project_id'] ?? '');
        $issueId = (string) ($payload['external_issue_id'] ?? '');

        if ($projectId === '' || $issueId === '') {
            throw new SprintValidationException('external_project_id and external_issue_id are required.');
        }

        if (! $this->tracker instanceof NullIssueTracker) {
            $hydrated = $this->tracker->hydrate($actor->id(), [$issueId]);
            if ($hydrated === []) {
                throw new SprintValidationException('Issue could not be verified with the configured tracker.');
            }
        }

        $existing = SprintIssue::query()
            ->where('sprint_id', $sprint->id)
            ->where('tracker', $tracker)
            ->where('external_project_id', $projectId)
            ->where('external_issue_id', $issueId)
            ->where('added_by', $actor->id())
            ->first();

        if ($existing !== null) {
            return $existing;
        }

        return SprintIssue::query()->create([
            'sprint_id' => $sprint->id,
            'tracker' => $tracker,
            'external_project_id' => $projectId,
            'external_issue_id' => $issueId,
            'added_by' => $actor->id(),
            'added_at' => now(),
            'priority_id' => $payload['priority_id'] ?? config('sprint.default_priority_id'),
            'completion_status' => IssueCompletionStatus::Pending,
        ]);
    }

    public function unlink(SprintUser $actor, Sprint $sprint, SprintIssue $issue): void
    {
        if ((int) $issue->sprint_id !== (int) $sprint->id) {
            throw new SprintValidationException('Issue does not belong to this sprint.');
        }

        $owns = (string) $issue->added_by === (string) $actor->id();
        if (! $owns && ! $this->authorizer->canRemove($actor, $sprint)) {
            throw new SprintAuthorizationException('You are not allowed to unlink this issue.');
        }

        $issue->delete();
    }
}
