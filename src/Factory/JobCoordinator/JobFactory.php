<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Factory\JobCoordinator;

use SmartAssert\ApiClient\Data\JobCoordinator\Job\Job;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\Machine;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\Preparation;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\ResultsJob;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\SerializedSuite;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\WorkerJob;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\WorkerJobComponent;
use SmartAssert\ApiClient\Exception\IncompleteDataException;
use SmartAssert\ApiClient\Factory\AbstractFactory;

readonly class JobFactory extends AbstractFactory
{
    /**
     * @param array<mixed> $data
     *
     * @throws IncompleteDataException
     */
    public function create(array $data): Job
    {
        $id = $this->getNonEmptyString($data, 'id');
        $suiteId = $this->getNonEmptyString($data, 'suite_id');

        $maximumDurationInSeconds = $data['maximum_duration_in_seconds'] ?? 0;
        $maximumDurationInSeconds = is_int($maximumDurationInSeconds) ? $maximumDurationInSeconds : 0;
        if ($maximumDurationInSeconds < 1) {
            throw new IncompleteDataException($data, 'maximum_duration_in_seconds');
        }

        $preparationData = $data['preparation'] ?? null;
        if (!is_array($preparationData)) {
            throw new IncompleteDataException($data, 'preparation');
        }

        try {
            $preparation = $this->createPreparation($preparationData);
        } catch (IncompleteDataException $e) {
            throw new IncompleteDataException($data, 'preparation.' . $e->missingKey);
        }

        $resultsJobData = $data['results_job'] ?? null;
        if (!is_array($resultsJobData)) {
            throw new IncompleteDataException($data, 'results_job');
        }

        $resultsJob = $this->createResultsJob($resultsJobData);

        $serializedSuiteData = $data['serialized_suite'] ?? null;
        if (!is_array($serializedSuiteData)) {
            throw new IncompleteDataException($data, 'serialized_suite');
        }

        $serializedSuite = $this->createSerializedSuite($serializedSuiteData);

        $machineData = $data['machine'] ?? null;
        if (!is_array($machineData)) {
            throw new IncompleteDataException($data, 'machine');
        }

        $machine = $this->createMachine($machineData);

        $workerJobData = $data['worker_job'] ?? null;
        if (!is_array($workerJobData)) {
            throw new IncompleteDataException($data, 'worker_job');
        }

        try {
            $workerJob = $this->createWorkerJob($workerJobData);
        } catch (IncompleteDataException $e) {
            throw new IncompleteDataException($data, 'worker_job.' . $e->missingKey);
        }

        return new Job(
            $id,
            $suiteId,
            $maximumDurationInSeconds,
            $preparation,
            $resultsJob,
            $serializedSuite,
            $machine,
            $workerJob,
        );
    }

    /**
     * @param array<mixed> $data
     *
     * @throws IncompleteDataException
     */
    private function createPreparation(array $data): Preparation
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

        return new Preparation($state, $filteredRequestStates);
    }

    /**
     * @param array<mixed> $data
     */
    private function createResultsJob(array $data): ResultsJob
    {
        return new ResultsJob(
            $this->getNullableNonEmptyString($data, 'state'),
            $this->getNullableNonEmptyString($data, 'end_state'),
        );
    }

    /**
     * @param array<mixed> $data
     */
    private function createSerializedSuite(array $data): SerializedSuite
    {
        return new SerializedSuite($this->getNullableNonEmptyString($data, 'state'));
    }

    /**
     * @param array<mixed> $data
     */
    private function createMachine(array $data): Machine
    {
        return new Machine(
            $this->getNullableNonEmptyString($data, 'state_category'),
            $this->getNullableNonEmptyString($data, 'ip_address'),
        );
    }

    /**
     * @param array<mixed> $data
     *
     * @throws IncompleteDataException
     */
    private function createWorkerJob(array $data): WorkerJob
    {
        $state = $this->getNonEmptyString($data, 'state');
        $isEndState = $this->getIsEndState($data);

        $componentDataCollection = $data['components'] ?? [];
        $componentDataCollection = is_array($componentDataCollection) ? $componentDataCollection : [];

        $components = [];

        foreach ($componentDataCollection as $componentName => $componentData) {
            if (is_string($componentName) && '' !== $componentName) {
                try {
                    $components[$componentName] = new WorkerJobComponent(
                        $this->getNonEmptyString($componentData, 'state'),
                        $this->getIsEndState($componentData),
                    );
                } catch (IncompleteDataException $e) {
                    throw new IncompleteDataException($data, 'components.' . $componentName . '.' . $e->missingKey);
                }
            }
        }

        return new WorkerJob($state, $isEndState, $components);
    }

    /**
     * @param array<mixed> $data
     */
    private function getIsEndState(array $data): bool
    {
        $value = $data['is_end_state'] ?? false;

        return is_bool($value) ? $value : false;
    }
}
