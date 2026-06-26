<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Data\Results;

interface TestInterface
{
    public function getPath(): string;

    public function getBrowser(): string;

    public function getUrl(): string;
}
