<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Factory\JobCoordinator;

use SmartAssert\ApiClient\Data\JobCoordinator\Job\WorkerJobCreationFailure;
use SmartAssert\ApiClient\Factory\AbstractFactory;

readonly class WorkerJobCreationFailureFactory extends AbstractFactory
{
    public function __construct(
        private ExceptionFactory $exceptionFactory,
    ) {}

    /**
     * @param array<mixed> $data
     */
    public function create(array $data): ?WorkerJobCreationFailure
    {
        if ([] === $data) {
            return null;
        }

        $stage = $data['stage'] ?? null;
        $stage = is_string($stage) ? $stage : '';

        $exceptionData = $data['exception'] ?? [];
        $exceptionData = is_array($exceptionData) ? $exceptionData : [];

        return new WorkerJobCreationFailure(
            $stage,
            $this->exceptionFactory->create($exceptionData),
        );
    }
}
