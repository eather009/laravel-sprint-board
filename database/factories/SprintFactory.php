<?php

declare(strict_types=1);

namespace Eather009\LaravelSprintBoard\Database\Factories;

use Eather009\LaravelSprintBoard\Enums\SprintStatus;
use Eather009\LaravelSprintBoard\Models\Sprint;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sprint>
 */
class SprintFactory extends Factory
{
    protected $model = Sprint::class;

    public function definition(): array
    {
        $start = now()->startOfDay();

        return [
            'leader_id' => 1,
            'created_by' => 1,
            'name' => fake()->sentence(3),
            'goal' => fake()->optional()->sentence(),
            'description' => fake()->optional()->paragraph(),
            'planning_note' => null,
            'retrospective' => null,
            'dashboard_settings' => null,
            'start_date' => $start->toDateString(),
            'end_date' => $start->copy()->addWeeks(2)->toDateString(),
            'status' => SprintStatus::Planning,
        ];
    }

    public function running(): static
    {
        return $this->state(fn (): array => [
            'status' => SprintStatus::Running,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (): array => [
            'status' => SprintStatus::Completed,
        ]);
    }
}
