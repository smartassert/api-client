<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Data\JobCoordinator\Job;

readonly class ComponentPreparation
{
    public function __construct(
        public string $state,
        public string $requestState,
        public ?ComponentPreparationFailure $failure = null,
    ) {}
}
