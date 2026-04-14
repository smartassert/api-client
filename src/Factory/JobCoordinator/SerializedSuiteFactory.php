<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Factory\JobCoordinator;

use SmartAssert\ApiClient\Data\JobCoordinator\Job\SerializedSuite;
use SmartAssert\ApiClient\Exception\IncompleteDataException;
use SmartAssert\ApiClient\Factory\AbstractFactory;

readonly class SerializedSuiteFactory extends AbstractFactory
{
    public function __construct(
        private MetaStateFactory $metaStateFactory,
        private ServiceRequestFactory $serviceRequestFactory,
        private ComponentPreparationFactory $componentPreparationFactory,
    ) {}

    /**
     * @param array<mixed> $data
     *
     * @throws IncompleteDataException
     */
    public function create(array $data): SerializedSuite
    {
        $serviceRequestData = $data['requests'] ?? [];
        $serviceRequestData = is_array($serviceRequestData) ? $serviceRequestData : [];

        $preparationData = $data['preparation'] ?? [];
        $preparationData = is_array($preparationData) ? $preparationData : [];

        return new SerializedSuite(
            $this->getNullableNonEmptyString($data, 'state'),
            $this->metaStateFactory->create($data),
            $this->componentPreparationFactory->create($preparationData),
            $this->serviceRequestFactory->createCollection($serviceRequestData),
        );
    }
}
