<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Data\JobCoordinator\Job;

use Symfony\Component\Uid\Ulid;

readonly class Job
{
    /**
     * @param non-empty-string $id
     * @param non-empty-string $suiteId
     * @param positive-int     $maximumDurationInSeconds
     * @param ServiceRequest[] $serviceRequests
     */
    public function __construct(
        public string $id,
        public string $suiteId,
        public int $maximumDurationInSeconds,
        public Preparation $preparation,
        public ResultsJob $resultsJob,
        public SerializedSuite $serializedSuite,
        public Machine $machine,
        public WorkerJob $workerJob,
        public array $serviceRequests,
    ) {
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return (new Ulid($this->id))->getDateTime();
    }
}
