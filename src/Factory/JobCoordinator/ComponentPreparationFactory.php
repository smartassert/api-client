<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Factory\JobCoordinator;

use SmartAssert\ApiClient\Data\JobCoordinator\Job\ComponentPreparation;
use SmartAssert\ApiClient\Exception\Factory\IncompleteDataException;
use SmartAssert\ApiClient\Factory\AbstractFactory;

readonly class ComponentPreparationFactory extends AbstractFactory
{
    public function __construct(
        private ComponentPreparationFailureFactory $componentPreparationFailureFactory,
    ) {}

    /**
     * @param array<mixed> $data
     *
     * @throws IncompleteDataException
     */
    public function create(array $data): ComponentPreparation
    {
        $failureData = $data['failure'] ?? [];
        $failureData = is_array($failureData) ? $failureData : [];

        return new ComponentPreparation(
            $this->getNonEmptyString($data, 'state'),
            $this->getNonEmptyString($data, 'request_state'),
            $this->componentPreparationFailureFactory->create($failureData),
        );
    }
}
