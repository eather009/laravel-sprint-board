<?php

declare(strict_types=1);

namespace Eather009\LaravelSprintBoard\Trackers;

use Eather009\LaravelSprintBoard\Contracts\BacklogCredentials;
use Eather009\LaravelSprintBoard\Contracts\IssueTracker;
use Eather009\LaravelSprintBoard\Exceptions\SprintValidationException;
use Eather009\LaravelSprintBoard\Support\SafeUrl;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Default Backlog.com / Backlog.jp issue tracker driver (API v2).
 *
 * @see https://developer.nulab.com/docs/backlog/
 */
class BacklogIssueTracker implements IssueTracker
{
    public function __construct(
        protected BacklogCredentials $credentials,
    ) {}

    public function hydrate(int|string $userId, array $issueIds): array
    {
        if ($issueIds === []) {
            return [];
        }

        $client = $this->clientFor($userId);
        $payload = [];

        // Backlog supports filtering by issueId[] / issueKey[] in batches.
        foreach (array_chunk(array_values($issueIds), 100) as $chunk) {
            $query = [];
            foreach ($chunk as $id) {
                if (ctype_digit((string) $id)) {
                    $query['issueId[]'][] = (int) $id;
                } else {
                    $query['issueKey[]'][] = (string) $id;
                }
            }

            $response = $client->get('/api/v2/issues', $query);

            if (! $response->successful()) {
                throw new RuntimeException(
                    'Backlog hydrate failed with HTTP '.$response->status().'.'
                );
            }

            foreach ($response->json() ?? [] as $issue) {
                if (! is_array($issue)) {
                    continue;
                }
                $key = (string) ($issue['id'] ?? $issue['issueKey'] ?? '');
                if ($key === '') {
                    continue;
                }
                $normalized = $this->normalizeIssue($issue);
                $payload[(string) ($issue['id'] ?? $key)] = $normalized;
                if (isset($issue['issueKey'])) {
                    $payload[(string) $issue['issueKey']] = $normalized;
                }
            }
        }

        return $payload;
    }

    public function isClosed(array $payload): bool
    {
        $closedIds = config('sprint.backlog.closed_status_ids', [4, 5]);
        $statusId = $payload['statusId'] ?? $payload['status_id'] ?? null;

        return $statusId !== null && in_array((int) $statusId, $closedIds, true);
    }

    public function updatePriority(int|string $userId, string $externalIssueId, int $priorityId, array $context = []): void
    {
        $client = $this->clientFor($userId);

        $response = $client->asForm()->patch('/api/v2/issues/'.$externalIssueId, [
            'priorityId' => $priorityId,
        ]);

        if (! $response->successful()) {
            throw new RuntimeException(
                'Backlog priority update failed with HTTP '.$response->status().'.'
            );
        }
    }

    protected function clientFor(int|string $userId): PendingRequest
    {
        $creds = $this->credentials->forUser($userId);

        if ($creds === null) {
            throw new SprintValidationException(
                'Backlog credentials are not configured. Bind BacklogCredentials or set sprint.backlog.space_url and sprint.backlog.api_key.'
            );
        }

        $space = rtrim($creds['space'], '/');
        $apiKey = $creds['api_key'];

        if (! SafeUrl::isAllowed($space)) {
            throw new SprintValidationException('Backlog space URL must be http or https.');
        }

        return Http::baseUrl($space)
            ->acceptJson()
            ->timeout((int) config('sprint.backlog.http_timeout', 15))
            ->withQueryParameters(['apiKey' => $apiKey]);
    }

    /**
     * @param  array<string, mixed>  $issue
     * @return array<string, mixed>
     */
    protected function normalizeIssue(array $issue): array
    {
        $status = $issue['status'] ?? [];
        $priority = $issue['priority'] ?? [];

        return [
            'id' => $issue['id'] ?? null,
            'issueKey' => $issue['issueKey'] ?? null,
            'summary' => $issue['summary'] ?? null,
            'statusId' => is_array($status) ? ($status['id'] ?? null) : null,
            'status_id' => is_array($status) ? ($status['id'] ?? null) : null,
            'priorityId' => is_array($priority) ? ($priority['id'] ?? null) : null,
            'raw' => $issue,
        ];
    }
}
