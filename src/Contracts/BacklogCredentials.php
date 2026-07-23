<?php

declare(strict_types=1);

namespace Eather009\LaravelSprintBoard\Contracts;

/**
 * Host-supplied Backlog space URL + API key for a given user (or app-wide).
 */
interface BacklogCredentials
{
    /**
     * @return array{space: string, api_key: string}|null
     */
    public function forUser(int|string $userId): ?array;
}
