<?php

declare(strict_types=1);

namespace Eather009\LaravelSprintBoard\Services;

use Eather009\LaravelSprintBoard\Enums\SprintStatus;
use Eather009\LaravelSprintBoard\Models\Sprint;
use Illuminate\Support\Carbon;

class SprintStatusResolver
{
    public function resolve(Sprint $sprint, ?Carbon $now = null): SprintStatus
    {
        $now ??= Carbon::now()->startOfDay();
        $start = Carbon::parse($sprint->start_date)->startOfDay();
        $end = Carbon::parse($sprint->end_date)->startOfDay();

        if ($now->lt($start)) {
            return SprintStatus::Planning;
        }

        if ($now->gt($end)) {
            return SprintStatus::Completed;
        }

        return SprintStatus::Running;
    }

    public function apply(Sprint $sprint, ?Carbon $now = null): Sprint
    {
        $sprint->status = $this->resolve($sprint, $now);
        $sprint->save();

        return $sprint;
    }
}
