<?php

declare(strict_types=1);

namespace Eather009\LaravelSprintBoard\Tests\Support;

use Eather009\LaravelSprintBoard\Contracts\IssueTracker;

class FakeClosedIssueTracker implements IssueTracker
{
    public function hydrate(int|string $userId, array $issueIds): array
    {
        $out = [];
        foreach ($issueIds as $id) {
            $out[(string) $id] = [
                'id' => (string) $id,
                'statusId' => 4,
                'summary' => 'Closed issue '.$id,
            ];
        }

        return $out;
    }

    public function isClosed(array $payload): bool
    {
        return (int) ($payload['statusId'] ?? 0) === 4;
    }

    public function updatePriority(int|string $userId, string $externalIssueId, int $priorityId, array $context = []): void
    {
        // no-op supported
    }
}
