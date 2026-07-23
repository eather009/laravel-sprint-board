<?php

declare(strict_types=1);

namespace Eather009\LaravelSprintBoard\Support;

use Eather009\LaravelSprintBoard\Contracts\SprintUser;
use Eather009\LaravelSprintBoard\Contracts\UserDirectory;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class EloquentUserDirectory implements UserDirectory
{
    /**
     * @return list<SprintUser>
     */
    public function searchEmployees(?string $query = null): array
    {
        $class = (string) config('sprint.user_model', 'App\\Models\\User');

        /** @var Builder<Model> $builder */
        $builder = $class::query();

        if ($query !== null && $query !== '') {
            $builder->where(function (Builder $q) use ($query): void {
                $q->where('name', 'like', '%'.$query.'%')
                    ->orWhere('email', 'like', '%'.$query.'%');
            });
        }

        $users = [];

        foreach ($builder->limit(50)->get() as $user) {
            if ($user instanceof Authenticatable && $user instanceof Model) {
                $users[] = new EloquentSprintUser($user);
            }
        }

        return $users;
    }

    public function find(int|string $userId): ?SprintUser
    {
        $class = (string) config('sprint.user_model', 'App\\Models\\User');
        $user = $class::query()->find($userId);

        if (! $user instanceof Authenticatable || ! $user instanceof Model) {
            return null;
        }

        return new EloquentSprintUser($user);
    }
}
