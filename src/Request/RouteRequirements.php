<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Request;

readonly class RouteRequirements
{
    /**
     * @param array<mixed> $parameters
     */
    public function __construct(
        public string $name,
        public array $parameters = [],
    ) {}
}
