<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Data\JobCoordinator\Job;

readonly class Components
{
    public function __construct(
        public ?ResultsJob $resultsJob,
        public ?SerializedSuite $serializedSuite,
        public ?Machine $machine,
        public WorkerJob $workerJob,
    ) {}
}
