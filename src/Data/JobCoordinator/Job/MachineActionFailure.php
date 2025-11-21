<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Data\JobCoordinator\Job;

readonly class MachineActionFailure
{
    /**
     * @param non-empty-string            $action
     * @param non-empty-string            $type
     * @param null|non-empty-array<mixed> $context
     */
    public function __construct(
        public string $action,
        public string $type,
        public ?array $context,
    ) {}
}
