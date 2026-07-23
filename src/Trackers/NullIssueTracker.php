<?php

declare(strict_types=1);

namespace Eather009\LaravelSprintBoard\Trackers;

use Eather009\LaravelSprintBoard\Contracts\IssueTracker;

/**
 * No-op tracker for hosts / tests without remote HTTP.
 */
class NullIssueTracker implements IssueTracker
{
    public function hydrate(int|string $userId, array $issueIds): array
    {
        $payload = [];

        foreach ($issueIds as $issueId) {
            $payload[(string) $issueId] = [
                'id' => (string) $issueId,
                'statusId' => null,
            ];
        }

        return $payload;
    }

    public function isClosed(array $payload): bool
    {
        return false;
    }

    public function updatePriority(int|string $userId, string $externalIssueId, int $priorityId, array $context = []): void
    {
        // no-op
    }
}
