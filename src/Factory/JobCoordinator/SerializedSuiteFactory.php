<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Factory\JobCoordinator;

use SmartAssert\ApiClient\Data\JobCoordinator\Job\SerializedSuite;
use SmartAssert\ApiClient\Factory\AbstractFactory;

readonly class SerializedSuiteFactory extends AbstractFactory
{
    public function __construct(
        private MetaStateFactory $metaStateFactory,
    ) {}

    /**
     * @param array<mixed> $data
     */
    public function create(array $data): ?SerializedSuite
    {
        $state = $this->getNullableNonEmptyString($data, 'state');
        if (null === $state) {
            return null;
        }

        return new SerializedSuite($state, $this->metaStateFactory->create($data));
    }
}
