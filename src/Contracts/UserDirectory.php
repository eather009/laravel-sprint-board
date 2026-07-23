<?php

declare(strict_types=1);

namespace Eather009\LaravelSprintBoard\Contracts;

use Illuminate\Support\Collection;

interface UserDirectory
{
    /**
     * Search host users that can be added as sprint members.
     *
     * @return Collection<int, SprintUser>
     */
    public function searchEmployees(?string $query = null): Collection;

    public function find(int|string $userId): ?SprintUser;
}
