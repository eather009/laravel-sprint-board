<?php

declare(strict_types=1);

namespace Eather009\LaravelSprintBoard\Tests\Feature;

use Eather009\LaravelSprintBoard\Contracts\IssueTracker;
use Eather009\LaravelSprintBoard\LaravelSprintBoardServiceProvider;
use Eather009\LaravelSprintBoard\Tests\TestCase;
use Eather009\LaravelSprintBoard\Trackers\BacklogIssueTracker;

class PackageBootsTest extends TestCase
{
    public function test_service_provider_is_registered(): void
    {
        $this->assertTrue(
            $this->app->providerIsLoaded(LaravelSprintBoardServiceProvider::class)
        );
    }

    public function test_sprint_config_is_merged(): void
    {
        $this->assertSame('backlog', config('sprint.tracker_default'));
        $this->assertSame('api/sprints', config('sprint.route_prefix'));
        $this->assertContains('auth:sanctum', config('sprint.middleware'));
    }

    public function test_default_issue_tracker_is_backlog(): void
    {
        $tracker = $this->app->make(IssueTracker::class);

        $this->assertInstanceOf(BacklogIssueTracker::class, $tracker);
    }

    public function test_backlog_tracker_detects_closed_status(): void
    {
        /** @var BacklogIssueTracker $tracker */
        $tracker = $this->app->make(IssueTracker::class);

        $this->assertTrue($tracker->isClosed(['statusId' => 4]));
        $this->assertFalse($tracker->isClosed(['statusId' => 1]));
    }
}
