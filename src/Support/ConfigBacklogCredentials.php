<?php

declare(strict_types=1);

namespace Eather009\LaravelSprintBoard\Support;

use Eather009\LaravelSprintBoard\Contracts\BacklogCredentials;

/**
 * Reads space URL + API key from config/env (app-wide; host may bind a per-user resolver).
 */
class ConfigBacklogCredentials implements BacklogCredentials
{
    public function forUser(int|string $userId): ?array
    {
        $space = (string) config('sprint.backlog.space_url', '');
        $apiKey = (string) config('sprint.backlog.api_key', '');

        if ($space === '' || $apiKey === '') {
            return null;
        }

        if (! SafeUrl::isAllowed($space)) {
            return null;
        }

        return [
            'space' => rtrim($space, '/'),
            'api_key' => $apiKey,
        ];
    }
}
