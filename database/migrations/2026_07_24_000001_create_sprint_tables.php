<?php

declare(strict_types=1);

use Eather009\LaravelSprintBoard\Support\SprintTable;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $sprints = SprintTable::sprints();
        $members = SprintTable::members();
        $issues = SprintTable::issues();

        if (! Schema::hasTable($sprints)) {
            Schema::create($sprints, function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('leader_id')->index();
                $table->unsignedBigInteger('created_by')->index();
                $table->string('name');
                $table->text('goal')->nullable();
                $table->text('description')->nullable();
                $table->text('planning_note')->nullable();
                $table->json('retrospective')->nullable();
                $table->json('dashboard_settings')->nullable();
                $table->date('start_date');
                $table->date('end_date');
                $table->string('status')->default('planning')->index();
                $table->timestamps();
            });
        } else {
            Schema::table($sprints, function (Blueprint $table) use ($sprints): void {
                if (! Schema::hasColumn($sprints, 'retrospective')) {
                    $table->json('retrospective')->nullable();
                }
                if (! Schema::hasColumn($sprints, 'dashboard_settings')) {
                    $table->json('dashboard_settings')->nullable();
                }
            });
        }

        if (! Schema::hasTable($members)) {
            Schema::create($members, function (Blueprint $table) use ($sprints): void {
                $table->id();
                $table->foreignId('sprint_id')->constrained($sprints)->cascadeOnDelete();
                $table->unsignedBigInteger('user_id')->index();
                $table->string('display_name');
                $table->string('role')->default('member');
                $table->timestamps();

                $table->unique(['sprint_id', 'user_id']);
            });
        }

        if (! Schema::hasTable($issues)) {
            Schema::create($issues, function (Blueprint $table) use ($sprints): void {
                $table->id();
                $table->foreignId('sprint_id')->constrained($sprints)->cascadeOnDelete();
                $table->string('tracker')->default('backlog');
                $table->string('external_project_id');
                $table->string('external_issue_id');
                $table->unsignedBigInteger('added_by')->index();
                $table->timestamp('added_at')->useCurrent();
                $table->unsignedInteger('priority_id')->nullable();
                $table->string('completion_status')->default('pending')->index();
                $table->text('completion_note')->nullable();
                $table->unsignedBigInteger('completion_updated_by')->nullable();
                $table->timestamp('completion_updated_at')->nullable();
                $table->timestamps();

                $table->unique(
                    ['sprint_id', 'tracker', 'external_project_id', 'external_issue_id', 'added_by'],
                    'sprint_issues_link_unique'
                );
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists(SprintTable::issues());
        Schema::dropIfExists(SprintTable::members());
        Schema::dropIfExists(SprintTable::sprints());
    }
};
