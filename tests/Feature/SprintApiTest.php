<?php

declare(strict_types=1);

namespace Eather009\LaravelSprintBoard\Tests\Feature;

use Eather009\LaravelSprintBoard\Contracts\IssueTracker;
use Eather009\LaravelSprintBoard\Enums\IssueCompletionStatus;
use Eather009\LaravelSprintBoard\Models\SprintIssue;
use Eather009\LaravelSprintBoard\Tests\Models\User;
use Eather009\LaravelSprintBoard\Tests\Support\FakeClosedIssueTracker;
use Eather009\LaravelSprintBoard\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SprintApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_crud_sprint_via_api(): void
    {
        $user = User::factory()->create();

        $create = $this->actingAs($user)->postJson('/api/sprints', [
            'name' => 'API Sprint',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addWeek()->toDateString(),
        ]);

        $create->assertCreated()
            ->assertJsonPath('data.name', 'API Sprint');

        $id = $create->json('data.id');

        $this->actingAs($user)
            ->getJson('/api/sprints/'.$id)
            ->assertOk()
            ->assertJsonPath('data.id', $id);

        $this->actingAs($user)
            ->putJson('/api/sprints/'.$id, ['name' => 'Renamed'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Renamed');

        $this->actingAs($user)
            ->getJson('/api/sprints')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->actingAs($user)
            ->deleteJson('/api/sprints/'.$id)
            ->assertOk();
    }

    public function test_link_issue_completion_and_dashboard(): void
    {
        $user = User::factory()->create();

        $id = $this->actingAs($user)->postJson('/api/sprints', [
            'name' => 'With Issues',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addWeek()->toDateString(),
        ])->json('data.id');

        $issue = $this->actingAs($user)->postJson('/api/sprints/'.$id.'/issues', [
            'external_project_id' => '10',
            'external_issue_id' => '200',
        ])->assertCreated()->json('data');

        $this->actingAs($user)->putJson('/api/sprints/'.$id.'/issues/'.$issue['id'].'/completion', [
            'completion_status' => 'completed',
            'completion_note' => 'Done',
        ])->assertOk()->assertJsonPath('data.completion_status', 'completed');

        $this->actingAs($user)
            ->getJson('/api/sprints/'.$id.'/dashboard')
            ->assertOk()
            ->assertJsonPath('data.widgets.progress.completed', 1);

        $this->actingAs($user)
            ->get('/api/sprints/'.$id.'/export/summary.txt')
            ->assertOk()
            ->assertHeader('content-type', 'text/plain; charset=UTF-8');
    }

    public function test_refresh_marks_closed_issues_completed_with_fake_tracker(): void
    {
        $this->app->instance(IssueTracker::class, new FakeClosedIssueTracker);

        $user = User::factory()->create();
        $id = $this->actingAs($user)->postJson('/api/sprints', [
            'name' => 'Sync',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(5)->toDateString(),
        ])->json('data.id');

        $issueId = $this->actingAs($user)->postJson('/api/sprints/'.$id.'/issues', [
            'external_project_id' => '1',
            'external_issue_id' => '42',
        ])->json('data.id');

        $this->actingAs($user)
            ->postJson('/api/sprints/'.$id.'/issues/refresh')
            ->assertOk()
            ->assertJsonPath('data.completed', 1);

        $this->assertSame(
            IssueCompletionStatus::Completed,
            SprintIssue::query()->find($issueId)->completion_status
        );
    }

    public function test_priority_sync_returns_501_for_null_tracker(): void
    {
        $user = User::factory()->create();
        $id = $this->actingAs($user)->postJson('/api/sprints', [
            'name' => 'Prio',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(2)->toDateString(),
        ])->json('data.id');

        $this->actingAs($user)
            ->postJson('/api/sprints/'.$id.'/issues/priority-sync')
            ->assertStatus(501);
    }

    public function test_guest_is_unauthorized(): void
    {
        $this->getJson('/api/sprints')->assertUnauthorized();
    }
}
