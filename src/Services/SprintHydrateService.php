<?php

declare(strict_types=1);

namespace Eather009\LaravelSprintBoard\Services;

use Eather009\LaravelSprintBoard\Contracts\IssueTracker;
use Eather009\LaravelSprintBoard\Contracts\SprintAuthorizer;
use Eather009\LaravelSprintBoard\Contracts\SprintUser;
use Eather009\LaravelSprintBoard\Enums\IssueCompletionStatus;
use Eather009\LaravelSprintBoard\Exceptions\SprintAuthorizationException;
use Eather009\LaravelSprintBoard\Models\Sprint;
use Eather009\LaravelSprintBoard\Models\SprintIssue;
use Illuminate\Support\Facades\Cache;

class SprintHydrateService
{
    public function __construct(
        protected IssueTracker $tracker,
        protected SprintAuthorizer $authorizer,
    ) {}

    /**
     * @return array<string, array<string, mixed>>
     */
    public function hydrate(SprintUser $actor, Sprint $sprint, bool $bust = false): array
    {
        if (! $this->authorizer->canView($actor, $sprint)) {
            throw new SprintAuthorizationException('You are not allowed to view this sprint.');
        }

        $issueIds = $sprint->issues()
            ->pluck('external_issue_id')
            ->unique()
            ->values()
            ->all();

        if ($issueIds === []) {
            return [];
        }

        $key = $this->cacheKey($sprint);
        $ttl = (int) config('sprint.backlog.hydrate_cache_ttl_hours', 3) * 3600;

        if ($bust) {
            Cache::forget($key);
        }

        return Cache::remember($key, $ttl, function () use ($actor, $issueIds): array {
            return $this->tracker->hydrate($actor->id(), $issueIds);
        });
    }

    public function forget(Sprint $sprint): void
    {
        Cache::forget($this->cacheKey($sprint));
    }

    protected function cacheKey(Sprint $sprint): string
    {
        return 'sprint-board:hydrate:'.$sprint->getKey();
    }

    /**
     * Merge local issues with hydrate payloads keyed by external_issue_id.
     *
     * @param  array<string, array<string, mixed>>  $hydrated
     * @return array<int, array<string, mixed>>
     */
    public function mergeIssues(Sprint $sprint, array $hydrated = []): array
    {
        return $sprint->issues->map(function (SprintIssue $issue) use ($hydrated): array {
            $meta = $hydrated[(string) $issue->external_issue_id] ?? null;

            return [
                'id' => $issue->id,
                'tracker' => $issue->tracker,
                'external_project_id' => $issue->external_project_id,
                'external_issue_id' => $issue->external_issue_id,
                'added_by' => $issue->added_by,
                'priority_id' => $issue->priority_id,
                'completion_status' => $issue->completion_status instanceof IssueCompletionStatus
                    ? $issue->completion_status->value
                    : $issue->completion_status,
                'completion_note' => $issue->completion_note,
                'external' => $meta,
            ];
        })->all();
    }
}
