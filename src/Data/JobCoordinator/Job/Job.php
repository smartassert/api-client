<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Data\JobCoordinator\Job;

readonly class Job implements HasMetastateInterface
{
    use HasMetaStateTrait;

    /**
     * @param ServiceRequest[] $serviceRequests
     */
    public function __construct(
        public Summary $summary,
        public Preparation $preparation,
        public MetaState $metaState,
        public Components $components,
        public array $serviceRequests,
    ) {}

    public function getResultsJob(): ?ResultsJob
    {
        $component = $this->components->get('results-job');

        return $component instanceof ResultsJob ? $component : null;
    }

    public function getSerializedSuite(): ?SerializedSuite
    {
        $component = $this->components->get('serialized-suite');

        return $component instanceof SerializedSuite ? $component : null;
    }

    public function getMachine(): ?Machine
    {
        $component = $this->components->get('machine');

        return $component instanceof Machine ? $component : null;
    }

    public function getWorkerJob(): ?WorkerJob
    {
        $component = $this->components->get('worker-job');

        return $component instanceof WorkerJob ? $component : null;
    }
}
