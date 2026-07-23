<?php

declare(strict_types=1);

namespace Eather009\LaravelSprintBoard\Contracts;

interface UserDirectory
{
    /**
     * Search host users that can be added as sprint members.
     *
     * @return list<SprintUser>
     */
    public function searchEmployees(?string $query = null): array;

    public function find(int|string $userId): ?SprintUser;
}
