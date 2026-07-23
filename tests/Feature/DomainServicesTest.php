<?php

declare(strict_types=1);

namespace Eather009\LaravelSprintBoard\Tests\Feature;

use Eather009\LaravelSprintBoard\Enums\IssueCompletionStatus;
use Eather009\LaravelSprintBoard\Enums\SprintMemberRole;
use Eather009\LaravelSprintBoard\Enums\SprintStatus;
use Eather009\LaravelSprintBoard\Exceptions\SprintAuthorizationException;
use Eather009\LaravelSprintBoard\Models\SprintIssue;
use Eather009\LaravelSprintBoard\Services\SprintCompletionService;
use Eather009\LaravelSprintBoard\Services\SprintIssueService;
use Eather009\LaravelSprintBoard\Services\SprintService;
use Eather009\LaravelSprintBoard\Services\SprintStatusResolver;
use Eather009\LaravelSprintBoard\Support\EloquentSprintUser;
use Eather009\LaravelSprintBoard\Tests\Models\User;
use Eather009\LaravelSprintBoard\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

class DomainServicesTest extends TestCase
{
    use RefreshDatabase;

    public function test_status_resolver_derives_planning_running_completed(): void
    {
        $resolver = $this->app->make(SprintStatusResolver::class);
        $leader = User::factory()->create();
        $actor = new EloquentSprintUser($leader);

        $service = $this->app->make(SprintService::class);
        $sprint = $service->create($actor, [
            'name' => 'Demo',
            'start_date' => '2030-01-10',
            'end_date' => '2030-01-20',
        ]);

        $this->assertSame(
            SprintStatus::Planning,
            $resolver->resolve($sprint, Carbon::parse('2030-01-01'))
        );
        $this->assertSame(
            SprintStatus::Running,
            $resolver->resolve($sprint, Carbon::parse('2030-01-15'))
        );
        $this->assertSame(
            SprintStatus::Completed,
            $resolver->resolve($sprint, Carbon::parse('2030-01-25'))
        );
    }

    public function test_create_sprint_syncs_leader_and_members(): void
    {
        $leader = User::factory()->create(['name' => 'Leader']);
        $member = User::factory()->create(['name' => 'Member']);
        $actor = new EloquentSprintUser($leader);

        $sprint = $this->app->make(SprintService::class)->create($actor, [
            'name' => 'Sprint A',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addWeek()->toDateString(),
        ], [
            ['user_id' => $member->id, 'role' => 'member'],
        ]);

        $this->assertSame(SprintStatus::Running, $sprint->status);
        $this->assertCount(2, $sprint->members);
        $this->assertTrue(
            $sprint->members->contains(
                fn ($row): bool => (int) $row->user_id === (int) $leader->id
                    && $row->role === SprintMemberRole::Leader
            )
        );
        $this->assertTrue(
            $sprint->members->contains(
                fn ($row): bool => (int) $row->user_id === (int) $member->id
                    && $row->role === SprintMemberRole::Member
            )
        );
    }

    public function test_member_cannot_manage_sprint(): void
    {
        $leader = User::factory()->create();
        $member = User::factory()->create();
        $leaderActor = new EloquentSprintUser($leader);
        $memberActor = new EloquentSprintUser($member);

        $service = $this->app->make(SprintService::class);
        $sprint = $service->create($leaderActor, [
            'name' => 'Locked',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addWeek()->toDateString(),
        ], [
            ['user_id' => $member->id],
        ]);

        $this->expectException(SprintAuthorizationException::class);
        $service->update($memberActor, $sprint, ['name' => 'Hacked']);
    }

    public function test_link_issue_and_completion_syncs_siblings(): void
    {
        $leader = User::factory()->create();
        $member = User::factory()->create();
        $leaderActor = new EloquentSprintUser($leader);
        $memberActor = new EloquentSprintUser($member);

        $sprint = $this->app->make(SprintService::class)->create($leaderActor, [
            'name' => 'Issues',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addWeek()->toDateString(),
        ], [
            ['user_id' => $member->id],
        ]);

        $issues = $this->app->make(SprintIssueService::class);
        $leaderIssue = $issues->link($leaderActor, $sprint, [
            'external_project_id' => '100',
            'external_issue_id' => '555',
        ]);
        $memberIssue = $issues->link($memberActor, $sprint, [
            'external_project_id' => '100',
            'external_issue_id' => '555',
        ]);

        $this->assertNotSame($leaderIssue->id, $memberIssue->id);

        $updated = $this->app->make(SprintCompletionService::class)->update(
            $leaderActor,
            $sprint,
            $leaderIssue,
            IssueCompletionStatus::Completed,
            'Done'
        );

        $this->assertSame(IssueCompletionStatus::Completed, $updated->completion_status);
        $this->assertSame(
            IssueCompletionStatus::Completed,
            SprintIssue::query()->find($memberIssue->id)->completion_status
        );
        $this->assertSame('Done', SprintIssue::query()->find($memberIssue->id)->completion_note);
    }

    public function test_null_tracker_allows_link_without_remote_http(): void
    {
        $leader = User::factory()->create();
        $actor = new EloquentSprintUser($leader);
        $sprint = $this->app->make(SprintService::class)->create($actor, [
            'name' => 'Offline',
            'start_date' => now()->toDateString(),
            'end_date' => now()->addDays(3)->toDateString(),
        ]);

        $issue = $this->app->make(SprintIssueService::class)->link($actor, $sprint, [
            'external_project_id' => '1',
            'external_issue_id' => '99',
        ]);

        $this->assertTrue($issue->exists);
        $this->assertSame('backlog', $issue->tracker);
    }
}
