<?php

declare(strict_types=1);

namespace Eather009\LaravelSprintBoard\Support;

use Eather009\LaravelSprintBoard\Contracts\SprintUser;
use Eather009\LaravelSprintBoard\Contracts\UserDirectory;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class EloquentUserDirectory implements UserDirectory
{
    /**
     * @return Collection<int, SprintUser>
     */
    public function searchEmployees(?string $query = null): Collection
    {
        $class = (string) config('sprint.user_model', 'App\\Models\\User');

        /** @var Builder $builder */
        $builder = $class::query();

        if ($query !== null && $query !== '') {
            $builder->where(function (Builder $q) use ($query): void {
                $q->where('name', 'like', '%'.$query.'%')
                    ->orWhere('email', 'like', '%'.$query.'%');
            });
        }

        return $builder
            ->limit(50)
            ->get()
            ->filter(fn ($user): bool => $user instanceof Authenticatable && $user instanceof Model)
            ->map(fn (Authenticatable&Model $user): SprintUser => new EloquentSprintUser($user))
            ->values();
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
