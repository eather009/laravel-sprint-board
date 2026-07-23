<?php

declare(strict_types=1);

namespace Eather009\LaravelSprintBoard\Http\Controllers;

use Eather009\LaravelSprintBoard\Contracts\UserResolver;
use Eather009\LaravelSprintBoard\Models\Sprint;
use Eather009\LaravelSprintBoard\Services\SprintDashboardService;
use Eather009\LaravelSprintBoard\Services\SprintService;
use Illuminate\Http\JsonResponse;

class SprintDashboardController extends Controller
{
    public function show(
        Sprint $sprint,
        UserResolver $resolver,
        SprintService $sprints,
        SprintDashboardService $dashboard,
    ): JsonResponse {
        $actor = $this->actor($resolver);
        $sprint = $sprints->findForView($actor, $sprint->getKey());

        return response()->json(['data' => $dashboard->build($actor, $sprint)]);
    }
}
