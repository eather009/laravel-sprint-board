<?php

declare(strict_types=1);

namespace Eather009\LaravelSprintBoard\Support;

use Eather009\LaravelSprintBoard\Contracts\SprintUser;
use Eather009\LaravelSprintBoard\Contracts\UserResolver;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class EloquentUserResolver implements UserResolver
{
    public function current(): ?SprintUser
    {
        $user = Auth::user();

        if (! $user instanceof Authenticatable || ! $user instanceof Model) {
            return null;
        }

        return new EloquentSprintUser($user);
    }
}
