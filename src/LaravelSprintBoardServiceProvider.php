<?php

declare(strict_types=1);

namespace Eather009\LaravelSprintBoard;

use Eather009\LaravelSprintBoard\Contracts\IssueTracker;
use Eather009\LaravelSprintBoard\Trackers\BacklogIssueTracker;
use Illuminate\Support\ServiceProvider;

class LaravelSprintBoardServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/sprint.php',
            'sprint'
        );

        $this->app->singleton(IssueTracker::class, function ($app) {
            $default = config('sprint.tracker_default', 'backlog');

            return match ($default) {
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
}
