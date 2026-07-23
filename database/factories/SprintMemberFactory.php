<?php

declare(strict_types=1);

namespace Eather009\LaravelSprintBoard\Database\Factories;

use Eather009\LaravelSprintBoard\Enums\SprintMemberRole;
use Eather009\LaravelSprintBoard\Models\Sprint;
use Eather009\LaravelSprintBoard\Models\SprintMember;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SprintMember>
 */
class SprintMemberFactory extends Factory
{
    protected $model = SprintMember::class;

    public function definition(): array
    {
        return [
            'sprint_id' => Sprint::factory(),
            'user_id' => fake()->unique()->numberBetween(1, 10_000),
            'display_name' => fake()->name(),
            'role' => SprintMemberRole::Member,
        ];
    }

    public function leader(): static
    {
        return $this->state(fn (): array => [
            'role' => SprintMemberRole::Leader,
        ]);
    }
}
