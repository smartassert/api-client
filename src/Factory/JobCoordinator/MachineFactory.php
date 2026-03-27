<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Factory\JobCoordinator;

use SmartAssert\ApiClient\Data\JobCoordinator\Job\Machine;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\MachineActionFailure;
use SmartAssert\ApiClient\Factory\AbstractFactory;

readonly class MachineFactory extends AbstractFactory
{
    public function __construct(
        private MetaStateFactory $metaStateFactory,
    ) {}

    /**
     * @param array<mixed> $data
     */
    public function create(array $data): ?Machine
    {
        $stateCategory = $this->getNullableNonEmptyString($data, 'state_category');
        if (null === $stateCategory) {
            return null;
        }

        $actionFailureData = $data['action_failure'] ?? [];
        $actionFailureData = is_array($actionFailureData) ? $actionFailureData : [];

        return new Machine(
            $stateCategory,
            $this->getNullableNonEmptyString($data, 'ip_address'),
            $this->createMachineActionFailure($actionFailureData),
            $this->metaStateFactory->create($data),
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
