<?php

declare(strict_types=1);

namespace Eather009\LaravelSprintBoard\Contracts;

interface UserResolver
{
    public function current(): ?SprintUser;
}
