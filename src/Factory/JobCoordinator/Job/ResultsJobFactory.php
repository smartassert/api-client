<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Factory\JobCoordinator\Job;

use SmartAssert\ApiClient\Data\JobCoordinator\Job\ResultsJob;
use SmartAssert\ApiClient\Factory\AbstractFactory;

readonly class ResultsJobFactory extends AbstractFactory
{
    public function __construct(
        private MetaStateFactory $metaStateFactory,
    ) {}

    /**
     * @param array<mixed> $data
     */
    public function create(array $data): ?ResultsJob
    {
        $state = $this->getNullableNonEmptyString($data, 'state');
        if (null === $state) {
            return null;
        }

        return new ResultsJob(
            $state,
            $this->getNullableNonEmptyString($data, 'end_state'),
            $this->metaStateFactory->create($data),
        );
    }
}
