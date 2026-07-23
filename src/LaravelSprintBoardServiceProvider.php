<?php

declare(strict_types=1);

namespace Eather009\LaravelSprintBoard;

use Eather009\LaravelSprintBoard\Auth\DefaultSprintAuthorizer;
use Eather009\LaravelSprintBoard\Contracts\BacklogCredentials;
use Eather009\LaravelSprintBoard\Contracts\IssueTracker;
use Eather009\LaravelSprintBoard\Contracts\SprintAuthorizer;
use Eather009\LaravelSprintBoard\Contracts\UserDirectory;
use Eather009\LaravelSprintBoard\Contracts\UserResolver;
use Eather009\LaravelSprintBoard\Exceptions\SprintAuthorizationException;
use Eather009\LaravelSprintBoard\Exceptions\SprintValidationException;
use Eather009\LaravelSprintBoard\Services\SprintCompletionService;
use Eather009\LaravelSprintBoard\Services\SprintCompletionSyncService;
use Eather009\LaravelSprintBoard\Services\SprintDashboardService;
use Eather009\LaravelSprintBoard\Services\SprintHydrateService;
use Eather009\LaravelSprintBoard\Services\SprintIssueService;
use Eather009\LaravelSprintBoard\Services\SprintService;
use Eather009\LaravelSprintBoard\Services\SprintStatusResolver;
use Eather009\LaravelSprintBoard\Support\ConfigBacklogCredentials;
use Eather009\LaravelSprintBoard\Support\EloquentUserDirectory;
use Eather009\LaravelSprintBoard\Support\EloquentUserResolver;
use Eather009\LaravelSprintBoard\Trackers\BacklogIssueTracker;
use Eather009\LaravelSprintBoard\Trackers\NullIssueTracker;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\ServiceProvider;

class LaravelSprintBoardServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/sprint.php',
            'sprint'
        );

        $this->app->singleton(UserResolver::class, EloquentUserResolver::class);
        $this->app->singleton(UserDirectory::class, EloquentUserDirectory::class);
        $this->app->singleton(SprintAuthorizer::class, DefaultSprintAuthorizer::class);
        $this->app->singleton(BacklogCredentials::class, ConfigBacklogCredentials::class);
        $this->app->singleton(SprintStatusResolver::class);
        $this->app->singleton(SprintService::class);
        $this->app->singleton(SprintIssueService::class);
        $this->app->singleton(SprintCompletionService::class);
        $this->app->singleton(SprintHydrateService::class);
        $this->app->singleton(SprintCompletionSyncService::class);
        $this->app->singleton(SprintDashboardService::class);

        $this->app->singleton(IssueTracker::class, function ($app) {
            $default = config('sprint.tracker_default', 'backlog');

            return match ($default) {
                'null', null, '' => $app->make(NullIssueTracker::class),
                'backlog' => $app->make(BacklogIssueTracker::class),
                default => $app->make(BacklogIssueTracker::class),
            };
        });

        $this->app->alias(IssueTracker::class, 'sprint.tracker');
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'sprint');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->registerExceptionRenderers();

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/sprint.php' => config_path('sprint.php'),
            ], 'sprint-config');

            $this->publishes([
                __DIR__.'/../lang' => $this->app->langPath('vendor/sprint'),
            ], 'sprint-lang');

            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'sprint-migrations');
        }
    }

    protected function registerExceptionRenderers(): void
    {
        $this->callAfterResolving(ExceptionHandler::class, function (ExceptionHandler $handler): void {
            if (! method_exists($handler, 'renderable')) {
                return;
            }

            $handler->renderable(function (SprintAuthorizationException $e, $request) {
                return response()->json([
                    'message' => $e->getMessage() ?: __('sprint::sprint.unauthorized'),
                ], 403);
            });

            $handler->renderable(function (SprintValidationException $e, $request) {
                return response()->json([
                    'message' => $e->getMessage() ?: __('sprint::sprint.validation_failed'),
                ], 422);
            });
        });
    }
}
