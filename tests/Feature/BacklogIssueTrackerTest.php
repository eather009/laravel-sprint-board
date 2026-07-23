<?php

declare(strict_types=1);

namespace Eather009\LaravelSprintBoard\Tests\Feature;

use Eather009\LaravelSprintBoard\Exceptions\SprintValidationException;
use Eather009\LaravelSprintBoard\Tests\TestCase;
use Eather009\LaravelSprintBoard\Trackers\BacklogIssueTracker;
use Illuminate\Support\Facades\Http;

class BacklogIssueTrackerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'sprint.backlog.space_url' => 'https://example.backlog.com',
            'sprint.backlog.api_key' => 'test-api-key',
        ]);
    }

    public function test_hydrate_fetches_and_normalizes_issues(): void
    {
        Http::fake([
            'https://example.backlog.com/api/v2/issues*' => Http::response([
                [
                    'id' => 42,
                    'issueKey' => 'DEMO-42',
                    'summary' => 'Fix bug',
                    'status' => ['id' => 4, 'name' => 'Closed'],
                    'priority' => ['id' => 3, 'name' => 'Normal'],
                ],
            ], 200),
        ]);

        $tracker = $this->app->make(BacklogIssueTracker::class);
        $payload = $tracker->hydrate(1, ['42']);

        $this->assertTrue($tracker->isClosed($payload['42']));
        $this->assertSame('Fix bug', $payload['42']['summary']);
        $this->assertSame('DEMO-42', $payload['DEMO-42']['issueKey']);

        Http::assertSent(function ($request): bool {
            return str_contains($request->url(), '/api/v2/issues')
                && str_contains($request->url(), 'apiKey=test-api-key');
        });
    }

    public function test_update_priority_patches_issue(): void
    {
        Http::fake([
            'https://example.backlog.com/api/v2/issues/DEMO-1*' => Http::response(['id' => 1], 200),
        ]);

        $tracker = $this->app->make(BacklogIssueTracker::class);
        $tracker->updatePriority(1, 'DEMO-1', 2);

        Http::assertSent(function ($request): bool {
            return $request->method() === 'PATCH'
                && str_contains($request->url(), '/api/v2/issues/DEMO-1')
                && (
                    (int) ($request['priorityId'] ?? 0) === 2
                    || str_contains($request->body(), 'priorityId=2')
                );
        });
    }

    public function test_missing_credentials_throw_validation_exception(): void
    {
        config([
            'sprint.backlog.space_url' => null,
            'sprint.backlog.api_key' => null,
        ]);

        $this->expectException(SprintValidationException::class);

        $this->app->make(BacklogIssueTracker::class)->hydrate(1, ['1']);
    }
}
