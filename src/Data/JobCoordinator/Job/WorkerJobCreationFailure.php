<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Data\JobCoordinator\Job;

readonly class WorkerJobCreationFailure
{
    public function __construct(
        public string $stage,
        public Exception $exception,
    ) {}
}
