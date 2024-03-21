<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Data\JobCoordinator\Job;

readonly class ServiceRequest
{
    /**
     * @param non-empty-string        $type
     * @param ServiceRequestAttempt[] $attempts
     */
    public function __construct(
        public string $type,
        public array $attempts,
    ) {
    }
}
