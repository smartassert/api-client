<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Data\JobCoordinator\Job;

readonly class WorkerJob
{
    /**
     * @param non-empty-string                            $state
     * @param array<non-empty-string, WorkerJobComponent> $componentStates
     */
    public function __construct(
        public string $state,
        public bool $isEndState,
        public array $componentStates,
    ) {
    }
}
