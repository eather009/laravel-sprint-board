<?php

declare(strict_types=1);

namespace Eather009\LaravelSprintBoard\Support;

final class SprintTable
{
    public static function name(string $base): string
    {
        return (string) config('sprint.table_prefix', '').$base;
    }

    public static function sprints(): string
    {
        return self::name('sprints');
    }

    public static function members(): string
    {
        return self::name('sprint_members');
    }

    public static function issues(): string
    {
        return self::name('sprint_issues');
    }
}
