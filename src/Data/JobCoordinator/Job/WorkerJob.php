<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Data\JobCoordinator\Job;

readonly class WorkerJob implements HasMetastateInterface, IsComponentInterface
{
    use HasMetaStateTrait;

    /**
     * @param non-empty-string                            $state
     * @param array<non-empty-string, WorkerJobComponent> $componentStates
     * @param ServiceRequest[]                            $serviceRequests
     */
    public function __construct(
        public string $state,
        public MetaState $metaState,
        public ComponentPreparation $preparation,
        public array $serviceRequests,
        public array $componentStates,
        public ?WorkerJobCreationFailure $creationFailure,
    ) {}
}
