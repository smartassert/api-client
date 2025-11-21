<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Functional\Client;

readonly class ExpectedRequestProperties
{
    /**
     * @param non-empty-string $method
     * @param non-empty-string $url
     */
    public function __construct(
        public string $method,
        public string $url,
    ) {}
}
