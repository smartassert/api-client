<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Data\JobCoordinator\Job;

readonly class ResultsJob implements HasMetastateInterface, IsComponentInterface
{
    use HasMetaStateTrait;

    /**
     * @param ?non-empty-string $state
     * @param ?non-empty-string $endState
     * @param ServiceRequest[]  $serviceRequests
     */
    public function __construct(
        public ?string $state,
        public ?string $endState,
        public MetaState $metaState,
        public ComponentPreparation $preparation,
        public array $serviceRequests,
        public bool $hasEvents,
    ) {}
}
