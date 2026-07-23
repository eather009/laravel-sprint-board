<?php

declare(strict_types=1);

namespace Eather009\LaravelSprintBoard\Database\Factories;

use Eather009\LaravelSprintBoard\Enums\IssueCompletionStatus;
use Eather009\LaravelSprintBoard\Models\Sprint;
use Eather009\LaravelSprintBoard\Models\SprintIssue;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SprintIssue>
 */
class SprintIssueFactory extends Factory
{
    protected $model = SprintIssue::class;

    public function definition(): array
    {
        return [
            'sprint_id' => Sprint::factory(),
            'tracker' => 'backlog',
            'external_project_id' => (string) fake()->numberBetween(1, 999),
            'external_issue_id' => (string) fake()->unique()->numberBetween(1000, 999_999),
            'added_by' => 1,
            'added_at' => now(),
            'priority_id' => 3,
            'completion_status' => IssueCompletionStatus::Pending,
            'completion_note' => null,
            'completion_updated_by' => null,
            'completion_updated_at' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (): array => [
            'completion_status' => IssueCompletionStatus::Completed,
            'completion_updated_by' => 1,
            'completion_updated_at' => now(),
        ]);
    }
}
