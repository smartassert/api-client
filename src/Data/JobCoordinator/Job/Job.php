<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Data\JobCoordinator\Job;

readonly class Job
{
    /**
     * @param ServiceRequest[] $serviceRequests
     */
    public function __construct(
        public Summary $summary,
        public Preparation $preparation,
        public Components $components,
        public array $serviceRequests,
        public MetaState $metaState,
    ) {}
}
