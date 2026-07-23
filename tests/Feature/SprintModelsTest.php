<?php

declare(strict_types=1);

namespace Eather009\LaravelSprintBoard\Tests\Feature;

use Eather009\LaravelSprintBoard\Enums\IssueCompletionStatus;
use Eather009\LaravelSprintBoard\Enums\SprintMemberRole;
use Eather009\LaravelSprintBoard\Enums\SprintStatus;
use Eather009\LaravelSprintBoard\Models\Sprint;
use Eather009\LaravelSprintBoard\Models\SprintIssue;
use Eather009\LaravelSprintBoard\Models\SprintMember;
use Eather009\LaravelSprintBoard\Support\SprintTable;
use Eather009\LaravelSprintBoard\Tests\Models\User;
use Eather009\LaravelSprintBoard\Tests\TestCase;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

class SprintModelsTest extends TestCase
{
    use RefreshDatabase;

    public function test_migrations_create_sprint_tables(): void
    {
        $this->assertTrue(Schema::hasTable(SprintTable::sprints()));
        $this->assertTrue(Schema::hasTable(SprintTable::members()));
        $this->assertTrue(Schema::hasTable(SprintTable::issues()));
    }

    public function test_migration_up_is_idempotent(): void
    {
        /** @var Migration $migration */
        $migration = require __DIR__.'/../../database/migrations/2026_07_24_000001_create_sprint_tables.php';

        $migration->up();

        $this->assertTrue(Schema::hasTable(SprintTable::sprints()));
        $this->assertTrue(Schema::hasTable(SprintTable::members()));
        $this->assertTrue(Schema::hasTable(SprintTable::issues()));
    }

    public function test_factory_creates_sprint_with_members_and_issues(): void
    {
        $leader = User::factory()->create();

        $sprint = Sprint::factory()->create([
            'leader_id' => $leader->id,
            'created_by' => $leader->id,
            'status' => SprintStatus::Running,
        ]);

        $member = SprintMember::factory()->leader()->create([
            'sprint_id' => $sprint->id,
            'user_id' => $leader->id,
            'display_name' => $leader->name,
        ]);

        $issue = SprintIssue::factory()->create([
            'sprint_id' => $sprint->id,
            'added_by' => $leader->id,
        ]);

        $this->assertTrue($sprint->exists);
        $this->assertSame(SprintStatus::Running, $sprint->fresh()->status);
        $this->assertTrue($sprint->members->contains($member));
        $this->assertTrue($sprint->issues->contains($issue));
        $this->assertSame(SprintMemberRole::Leader, $member->role);
        $this->assertSame(IssueCompletionStatus::Pending, $issue->completion_status);
        $this->assertSame('backlog', $issue->tracker);
    }

    public function test_table_prefix_is_applied(): void
    {
        config(['sprint.table_prefix' => 'pkg_']);

        $this->assertSame('pkg_sprints', SprintTable::sprints());
        $this->assertSame('pkg_sprint_members', SprintTable::members());
        $this->assertSame('pkg_sprint_issues', SprintTable::issues());
    }
}
