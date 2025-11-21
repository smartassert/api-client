<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Data\JobCoordinator\Job;

readonly class Preparation
{
    /**
     * @param non-empty-string                          $state
     * @param array<non-empty-string, non-empty-string> $requestStates
     */
    public function __construct(
        public string $state,
        public array $requestStates,
    ) {}
}
