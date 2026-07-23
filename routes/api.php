<?php

declare(strict_types=1);

use Eather009\LaravelSprintBoard\Http\Controllers\SprintController;
use Eather009\LaravelSprintBoard\Http\Controllers\SprintDashboardController;
use Eather009\LaravelSprintBoard\Http\Controllers\SprintIssueController;
use Eather009\LaravelSprintBoard\Models\Sprint;
use Eather009\LaravelSprintBoard\Models\SprintIssue;
use Illuminate\Support\Facades\Route;

Route::middleware(config('sprint.middleware', ['api', 'auth:sanctum']))
    ->prefix(config('sprint.route_prefix', 'api/sprints'))
    ->name('sprint.')
    ->group(function (): void {
        Route::bind('sprint', fn (string $value) => Sprint::query()->findOrFail($value));
        Route::bind('issue', fn (string $value) => SprintIssue::query()->findOrFail($value));

        Route::get('/', [SprintController::class, 'index'])->name('index');
        Route::post('/', [SprintController::class, 'store'])->name('store');
        Route::get('/{sprint}', [SprintController::class, 'show'])->name('show');
        Route::match(['put', 'patch'], '/{sprint}', [SprintController::class, 'update'])->name('update');
        Route::delete('/{sprint}', [SprintController::class, 'destroy'])->name('destroy');

        Route::get('/{sprint}/members', [SprintController::class, 'members'])->name('members.index');
        Route::put('/{sprint}/members', [SprintController::class, 'syncMembers'])->name('members.sync');

        Route::get('/{sprint}/issues', [SprintIssueController::class, 'index'])->name('issues.index');
        Route::post('/{sprint}/issues', [SprintIssueController::class, 'store'])->name('issues.store');
        Route::delete('/{sprint}/issues/{issue}', [SprintIssueController::class, 'destroy'])->name('issues.destroy');
        Route::put('/{sprint}/issues/{issue}/completion', [SprintIssueController::class, 'completion'])->name('issues.completion');
        Route::post('/{sprint}/issues/refresh', [SprintIssueController::class, 'refresh'])->name('issues.refresh');
        Route::post('/{sprint}/issues/priority-sync', [SprintIssueController::class, 'prioritySync'])->name('issues.priority-sync');

        Route::get('/{sprint}/dashboard', [SprintDashboardController::class, 'show'])->name('dashboard');
        Route::get('/{sprint}/retrospective', [SprintController::class, 'retrospective'])->name('retrospective.show');
        Route::put('/{sprint}/retrospective', [SprintController::class, 'updateRetrospective'])->name('retrospective.update');

        Route::get('/{sprint}/export/issues.csv', [SprintIssueController::class, 'exportCsv'])->name('export.issues');
        Route::get('/{sprint}/export/summary.txt', [SprintIssueController::class, 'exportSummary'])->name('export.summary');
    });
