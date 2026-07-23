<?php

declare(strict_types=1);

namespace Eather009\LaravelSprintBoard\Support;

use Eather009\LaravelSprintBoard\Contracts\SprintUser;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;

class EloquentSprintUser implements SprintUser
{
    public function __construct(
        protected Authenticatable&Model $user
    ) {}

    public function id(): int|string
    {
        return $this->user->getAuthIdentifier();
    }

    public function displayName(): string
    {
        $name = $this->user->getAttribute('name')
            ?? $this->user->getAttribute('display_name')
            ?? null;

        return $name !== null && $name !== ''
            ? (string) $name
            : (string) $this->id();
    }

    public function isSprintAdmin(): bool
    {
        $gate = config('sprint.admin_gate');

        if (is_string($gate) && $gate !== '' && Gate::forUser($this->user)->check($gate)) {
            return true;
        }

        return (bool) $this->user->getAttribute('is_sprint_admin');
    }

    public function model(): Authenticatable&Model
    {
        return $this->user;
    }
}
