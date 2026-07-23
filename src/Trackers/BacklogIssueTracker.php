<?php

declare(strict_types=1);

namespace Eather009\LaravelSprintBoard\Trackers;

use Eather009\LaravelSprintBoard\Contracts\IssueTracker;
use LogicException;

/**
 * Default Backlog driver. Hydrate / priority HTTP land in Phase 5; Phase 1 is a bindable stub.
 */
class BacklogIssueTracker implements IssueTracker
{
    public function hydrate(int|string $userId, array $issueIds): array
    {
        if ($issueIds === []) {
            return [];
        }

        throw new LogicException(
            'BacklogIssueTracker::hydrate is not implemented yet. Bind credentials and complete Phase 5, or use a fake tracker in tests.'
        );
    }

    public function isClosed(array $payload): bool
    {
        $closedIds = config('sprint.backlog.closed_status_ids', [4, 5]);
        $statusId = $payload['statusId'] ?? $payload['status_id'] ?? null;

        return $statusId !== null && in_array((int) $statusId, $closedIds, true);
    }

    public function updatePriority(int|string $userId, string $externalIssueId, int $priorityId, array $context = []): void
    {
        throw new LogicException(
            'BacklogIssueTracker::updatePriority is not implemented yet. Complete Phase 5 or bind a custom IssueTracker.'
        );
    }
}
