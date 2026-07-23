<?php

/**
 * Example host wiring for eather009/laravel-sprint-board.
 *
 * Place similar code in a service provider of your Laravel application.
 */

use Eather009\LaravelSprintBoard\Contracts\BacklogCredentials;
use Eather009\LaravelSprintBoard\Contracts\IssueTracker;
use Eather009\LaravelSprintBoard\Trackers\BacklogIssueTracker;
use Eather009\LaravelSprintBoard\Trackers\NullIssueTracker;

// Use NullIssueTracker while developing without Backlog:
// $this->app->bind(IssueTracker::class, NullIssueTracker::class);

// Or keep the default Backlog driver and supply credentials:
$this->app->bind(BacklogCredentials::class, function () {
    return new class implements BacklogCredentials
    {
        public function forUser(int|string $userId): ?array
        {
            return [
                'space' => env('BACKLOG_SPACE_URL'),
                'api_key' => env('BACKLOG_API_KEY'),
            ];
        }
    };
});

$this->app->bind(IssueTracker::class, BacklogIssueTracker::class);

/*
| API quick start (with Sanctum token):
|
| POST /api/sprints
| {
|   "name": "Sprint 1",
|   "start_date": "2026-08-01",
|   "end_date": "2026-08-14",
|   "members": [{"user_id": 2, "role": "member"}]
| }
|
| POST /api/sprints/{id}/issues
| {
|   "external_project_id": "123",
|   "external_issue_id": "456"
| }
|
| GET /api/sprints/{id}/dashboard
*/
