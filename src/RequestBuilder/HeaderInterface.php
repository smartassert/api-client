<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\RequestBuilder;

interface HeaderInterface
{
    /**
     * @return array<string, string>
     */
    public function toArray(): array;
}
