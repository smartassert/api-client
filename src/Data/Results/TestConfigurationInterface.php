<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Data\Results;

interface TestConfigurationInterface
{
    public function getBrowser(): string;

    public function getUrl(): string;
}
