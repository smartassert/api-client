<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Factory\JobCoordinator;

use SmartAssert\ApiClient\Data\JobCoordinator\Job\ResultsJob;
use SmartAssert\ApiClient\Exception\Factory\IncompleteDataException;
use SmartAssert\ApiClient\Factory\AbstractFactory;

readonly class ResultsJobFactory extends AbstractFactory
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
    public function create(array $data): ResultsJob
    {
        $serviceRequestData = $data['requests'] ?? [];
        $serviceRequestData = is_array($serviceRequestData) ? $serviceRequestData : [];

        $preparationData = $data['preparation'] ?? [];
        $preparationData = is_array($preparationData) ? $preparationData : [];

        $hasEvents = $data['has_events'] ?? false;
        $hasEvents = is_bool($hasEvents) ? $hasEvents : false;

        return new ResultsJob(
            $this->getNullableNonEmptyString($data, 'state'),
            $this->getNullableNonEmptyString($data, 'end_state'),
            $this->metaStateFactory->create($data),
            $this->componentPreparationFactory->create($preparationData),
            $this->serviceRequestFactory->createCollection($serviceRequestData),
            $hasEvents,
        );
    }
}
