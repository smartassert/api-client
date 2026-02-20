<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Data\JobCoordinator\Job;

readonly class SerializedSuite
{
    /**
     * @param non-empty-string $state
     */
    public function __construct(
        public string $state,
    ) {}
}
