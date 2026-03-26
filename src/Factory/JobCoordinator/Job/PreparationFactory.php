<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Factory\JobCoordinator\Job;

use SmartAssert\ApiClient\Data\JobCoordinator\Job\Preparation;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\PreparationFailure;
use SmartAssert\ApiClient\Exception\IncompleteDataException;
use SmartAssert\ApiClient\Factory\AbstractFactory;

readonly class PreparationFactory extends AbstractFactory
{
    public function __construct(
        private MetaStateFactory $metaStateFactory,
    ) {}

    /**
     * @param array<mixed> $data
     *
     * @throws IncompleteDataException
     */
    public function create(array $data): Preparation
    {
        $state = $this->getNonEmptyString($data, 'state');
        $requestStates = $data['request_states'] ?? [];
        $requestStates = is_array($requestStates) ? $requestStates : [];

        $filteredRequestStates = [];
        foreach ($requestStates as $componentName => $requestState) {
            if (
                is_string($componentName) && '' !== $componentName
                && is_string($requestState) && '' !== $requestState
            ) {
                $filteredRequestStates[$componentName] = $requestState;
            }
        }

        $componentFailures = $data['failures'] ?? [];
        $componentFailures = is_array($componentFailures) ? $componentFailures : [];

        $filteredComponentFailures = [];
        foreach ($componentFailures as $componentName => $failureData) {
            $failure = $this->createFailure(is_array($failureData) ? $failureData : []);

            if (null !== $failure) {
                $filteredComponentFailures[$componentName] = $failure;
            }
        }

        return new Preparation(
            $state,
            $this->metaStateFactory->create($data),
            $filteredRequestStates,
            $filteredComponentFailures,
        );
    }

    /**
     * @param array<mixed> $data
     */
    private function createFailure(array $data): ?PreparationFailure
    {
        $type = $data['type'] ?? null;
        $type = is_string($type) ? $type : null;
        if (null === $type) {
            return null;
        }

        $code = $data['code'] ?? null;
        $code = is_int($code) ? $code : null;
        if (null === $code) {
            return null;
        }

        $message = $data['message'] ?? null;
        $message = is_string($message) ? $message : null;
        if (null === $message) {
            return null;
        }

        return new PreparationFailure($type, $code, $message);
    }
}
