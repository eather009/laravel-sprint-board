<?php

declare(strict_types=1);

namespace Eather009\LaravelSprintBoard\Tests;

use Eather009\LaravelSprintBoard\LaravelSprintBoardServiceProvider;
use Eather009\LaravelSprintBoard\Tests\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
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
        $app['config']->set('sprint.tracker_default', 'null');
        $app['config']->set('sprint.user_model', User::class);
        $app['config']->set('sprint.table_prefix', '');
        $app['config']->set('sprint.middleware', ['api', 'auth']);
        $app['config']->set('auth.providers.users.model', User::class);
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        $app['config']->set('cache.default', 'array');
    }

    protected function defineDatabaseMigrations(): void
    {
        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->boolean('is_sprint_admin')->default(false);
            $table->timestamps();
        });
    }
}
