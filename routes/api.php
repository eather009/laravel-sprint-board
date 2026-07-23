<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Sprint Board API routes
|--------------------------------------------------------------------------
|
| Controllers land in Phase 4. Prefix and middleware come from config/sprint.php.
|
*/

Route::middleware(config('sprint.middleware', ['api', 'auth:sanctum']))
    ->prefix(config('sprint.route_prefix', 'api/sprints'))
    ->name('sprint.')
    ->group(function (): void {
        // Phase 4: CRUD + members + issues + dashboard + retrospective
    });
