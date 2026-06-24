<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Data\Results;

readonly class TestConfiguration implements TestConfigurationInterface
{
    public function __construct(
        private string $browser,
        private string $url,
    ) {}

    public function getBrowser(): string
    {
        return $this->browser;
    }

    public function getUrl(): string
    {
        return $this->url;
    }
}
