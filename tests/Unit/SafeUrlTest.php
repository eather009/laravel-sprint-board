<?php

declare(strict_types=1);

namespace Eather009\LaravelSprintBoard\Tests\Unit;

use Eather009\LaravelSprintBoard\Support\SafeUrl;
use Eather009\LaravelSprintBoard\Tests\TestCase;

class SafeUrlTest extends TestCase
{
    public function test_allows_http_https_only(): void
    {
        $this->assertTrue(SafeUrl::isAllowed('https://example.com/x'));
        $this->assertTrue(SafeUrl::isAllowed('http://example.com'));
        $this->assertFalse(SafeUrl::isAllowed('javascript:alert(1)'));
        $this->assertFalse(SafeUrl::isAllowed('ftp://files'));
        $this->assertFalse(SafeUrl::isAllowed(null));
    }
}
