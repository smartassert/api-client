<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Data\Results;

readonly class Test implements TestInterface
{
    public function __construct(
        private string $path,
        private string $browser,
        private string $url,
    ) {}

    public function getPath(): string
    {
        return $this->path;
    }

    public function getBrowser(): string
    {
        return $this->browser;
    }

    public function getUrl(): string
    {
        return $this->url;
    }
}
