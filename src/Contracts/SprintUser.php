<?php

declare(strict_types=1);

namespace Eather009\LaravelSprintBoard\Contracts;

interface SprintUser
{
    public function id(): int|string;

    public function displayName(): string;

    public function isSprintAdmin(): bool;
}
