<?php

declare(strict_types=1);

namespace Eather009\LaravelSprintBoard\Services;

use Eather009\LaravelSprintBoard\Contracts\SprintAuthorizer;
use Eather009\LaravelSprintBoard\Contracts\SprintUser;
use Eather009\LaravelSprintBoard\Enums\IssueCompletionStatus;
use Eather009\LaravelSprintBoard\Exceptions\SprintAuthorizationException;
use Eather009\LaravelSprintBoard\Models\Sprint;

class SprintDashboardService
{
    public function __construct(
        protected SprintAuthorizer $authorizer,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function build(SprintUser $actor, Sprint $sprint): array
    {
        if (! $this->authorizer->canView($actor, $sprint)) {
            throw new SprintAuthorizationException('You are not allowed to view this sprint.');
        }

        $issues = $sprint->issues()->get();
        $total = $issues->count();
        $completed = $issues->where('completion_status', IssueCompletionStatus::Completed)->count();

        $byMember = $issues->groupBy('added_by')->map(function ($group, $userId): array {
            $done = $group->where('completion_status', IssueCompletionStatus::Completed)->count();

            return [
                'user_id' => $userId,
                'total' => $group->count(),
                'completed' => $done,
                'progress' => $group->count() > 0 ? round($done / $group->count(), 2) : 0,
            ];
        })->values()->all();

        $byPriority = $issues->groupBy(fn ($issue) => (int) ($issue->priority_id ?? 0))
            ->map(fn ($group, $priorityId): array => [
                'priority_id' => (int) $priorityId,
                'total' => $group->count(),
                'completed' => $group->where('completion_status', IssueCompletionStatus::Completed)->count(),
            ])->values()->all();

        return [
            'sprint_id' => $sprint->id,
            'widgets' => [
                'progress' => [
                    'total' => $total,
                    'completed' => $completed,
                    'ratio' => $total > 0 ? round($completed / $total, 2) : 0,
                ],
                'by_member' => $byMember,
                'by_priority' => $byPriority,
                'completion' => [
                    'pending' => $issues->where('completion_status', IssueCompletionStatus::Pending)->count(),
                    'completed' => $completed,
                ],
            ],
        ];
    }
}
