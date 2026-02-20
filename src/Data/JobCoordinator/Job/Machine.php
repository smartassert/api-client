<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Data\JobCoordinator\Job;

readonly class Machine
{
    /**
     * @param non-empty-string  $stateCategory
     * @param ?non-empty-string $ipAddress
     */
    public function __construct(
        public string $stateCategory,
        public ?string $ipAddress,
        public ?MachineActionFailure $actionFailure,
    ) {}
}
