<?php

declare(strict_types=1);

namespace Eather009\LaravelSprintBoard\Contracts;

/**
 * External issue tracker driver (Backlog by default; GitHub/Jira/custom via bind).
 */
interface IssueTracker
{
    /**
     * Hydrate external issue metadata for the given issue identifiers.
     *
     * @param  array<int, string>  $issueIds
     * @return array<string, array<string, mixed>>
     */
    public function hydrate(int|string $userId, array $issueIds): array;

    /**
     * Whether the hydrated payload represents a closed / done issue.
     *
     * @param  array<string, mixed>  $payload
     */
    public function isClosed(array $payload): bool;

    /**
     * Update priority on the remote tracker when supported.
     *
     * @param  array<string, mixed>  $context
     */
    public function updatePriority(int|string $userId, string $externalIssueId, int $priorityId, array $context = []): void;
}
