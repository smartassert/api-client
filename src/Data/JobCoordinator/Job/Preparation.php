<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Data\JobCoordinator\Job;

readonly class Preparation implements HasMetastateInterface
{
    use HasMetaStateTrait;

    /**
     * @param non-empty-string                            $state
     * @param array<non-empty-string, non-empty-string>   $requestStates
     * @param array<non-empty-string, PreparationFailure> $componentFailures
     */
    public function __construct(
        public string $state,
        public MetaState $metaState,
        public array $requestStates,
        public array $componentFailures,
    ) {}
}
