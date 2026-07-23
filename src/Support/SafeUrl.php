<?php

declare(strict_types=1);

namespace Eather009\LaravelSprintBoard\Support;

final class SafeUrl
{
    public static function isAllowed(?string $url): bool
    {
        if ($url === null || $url === '') {
            return false;
        }

        $parts = parse_url($url);
        if ($parts === false || ! isset($parts['scheme'])) {
            return false;
        }

        return in_array(strtolower($parts['scheme']), ['http', 'https'], true);
    }
}
