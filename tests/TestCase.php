<?php

declare(strict_types=1);

namespace Eather009\LaravelSprintBoard\Tests;

use Eather009\LaravelSprintBoard\LaravelSprintBoardServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            LaravelSprintBoardServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('sprint.tracker_default', 'backlog');
    }
}
