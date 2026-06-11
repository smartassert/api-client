<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Factory\JobCoordinator;

use SmartAssert\ApiClient\Data\JobCoordinator\Job\Machine;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\MachineActionFailure;
use SmartAssert\ApiClient\Exception\Factory\IncompleteDataException;
use SmartAssert\ApiClient\Factory\AbstractFactory;

readonly class MachineFactory extends AbstractFactory
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
    public function create(array $data): Machine
    {
        $actionFailureData = $data['action_failure'] ?? [];
        $actionFailureData = is_array($actionFailureData) ? $actionFailureData : [];

        $serviceRequestData = $data['requests'] ?? [];
        $serviceRequestData = is_array($serviceRequestData) ? $serviceRequestData : [];

        $preparationData = $data['preparation'] ?? [];
        $preparationData = is_array($preparationData) ? $preparationData : [];

        return new Machine(
            $this->getNullableNonEmptyString($data, 'state_category'),
            $this->getNullableNonEmptyString($data, 'ip_address'),
            $this->createMachineActionFailure($actionFailureData),
            $this->metaStateFactory->create($data),
            $this->componentPreparationFactory->create($preparationData),
            $this->serviceRequestFactory->createCollection($serviceRequestData),
        );
    }

    /**
     * @param array<mixed> $data
     */
    private function createMachineActionFailure(array $data): ?MachineActionFailure
    {
        $action = $this->getNullableNonEmptyString($data, 'action');
        if (null === $action) {
            return null;
        }

        $type = $this->getNullableNonEmptyString($data, 'type');
        if (null === $type) {
            return null;
        }

        $context = $data['context'] ?? null;
        $context = is_array($context) ? $context : null;
        $context = [] === $context ? null : $context;

        return new MachineActionFailure($action, $type, $context);
    }
}
