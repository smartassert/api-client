<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Data\JobCoordinator\Job;

readonly class Components
{
    /**
     * @param array<string, IsComponentInterface> $components
     */
    public function __construct(
        public array $components,
    ) {}

    public function get(string $name): ?IsComponentInterface
    {
        return $this->components[$name] ?? null;
    }
}
