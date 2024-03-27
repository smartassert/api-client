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
        public ResultsJob $resultsJob,
        public SerializedSuite $serializedSuite,
        public Machine $machine,
        public WorkerJob $workerJob,
        public array $serviceRequests,
    ) {
    }
}
