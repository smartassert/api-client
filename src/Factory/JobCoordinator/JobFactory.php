<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Factory\JobCoordinator;

use SmartAssert\ApiClient\Data\JobCoordinator\Job\Job;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\Machine;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\MachineActionFailure;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\Preparation;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\ResultsJob;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\SerializedSuite;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\ServiceRequest;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\ServiceRequestAttempt;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\WorkerJob;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\WorkerJobComponent;
use SmartAssert\ApiClient\Exception\IncompleteDataException;
use SmartAssert\ApiClient\Factory\AbstractFactory;

readonly class JobFactory extends AbstractFactory
{
    public function __construct(
        private SummaryFactory $summaryFactory,
    ) {}

    /**
     * @param array<mixed> $data
     *
     * @throws IncompleteDataException
     */
    public function create(array $data): Job
    {
        $summary = $this->summaryFactory->create($data);

        $preparationData = $data['preparation'] ?? null;
        if (!is_array($preparationData)) {
            throw new IncompleteDataException($data, 'preparation');
        }

        try {
            $preparation = $this->createPreparation($preparationData);
        } catch (IncompleteDataException $e) {
            throw new IncompleteDataException($data, 'preparation.' . $e->missingKey);
        }

        if (!array_key_exists('results-job', $data)) {
            throw new IncompleteDataException($data, 'results-job');
        }

        $resultsJob = $this->createResultsJob($data['results-job'] ?? []);

        if (!array_key_exists('serialized-suite', $data)) {
            throw new IncompleteDataException($data, 'serialized-suite');
        }

        $serializedSuite = $this->createSerializedSuite($data['serialized-suite'] ?? []);

        if (!array_key_exists('machine', $data)) {
            throw new IncompleteDataException($data, 'machine');
        }

        $machine = $this->createMachine($data['machine'] ?? []);

        $workerJobData = $data['worker-job'] ?? null;
        if (!is_array($workerJobData)) {
            throw new IncompleteDataException($data, 'worker-job');
        }

        try {
            $workerJob = $this->createWorkerJob($workerJobData);
        } catch (IncompleteDataException $e) {
            throw new IncompleteDataException($data, 'worker_job.' . $e->missingKey);
        }

        $serviceRequestDataCollection = $data['service_requests'] ?? null;
        if (!is_array($serviceRequestDataCollection)) {
            throw new IncompleteDataException($data, 'service_requests');
        }

        try {
            $serviceRequests = $this->createServiceRequests($serviceRequestDataCollection);
        } catch (IncompleteDataException $e) {
            throw new IncompleteDataException($data, 'service_requests.' . $e->missingKey);
        }

        return new Job($summary, $preparation, $resultsJob, $serializedSuite, $machine, $workerJob, $serviceRequests);
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
    private function createResultsJob(array $data): ?ResultsJob
    {
        $state = $this->getNullableNonEmptyString($data, 'state');
        if (null === $state) {
            return null;
        }

        return new ResultsJob(
            $state,
            $this->getNullableNonEmptyString($data, 'end_state'),
        );
    }

    /**
     * @param array<mixed> $data
     */
    private function createSerializedSuite(array $data): ?SerializedSuite
    {
        $state = $this->getNullableNonEmptyString($data, 'state');
        if (null === $state) {
            return null;
        }

        return new SerializedSuite($state);
    }

    /**
     * @param array<mixed> $data
     */
    private function createMachine(array $data): Machine
    {
        $actionFailureData = $data['action_failure'] ?? [];
        $actionFailureData = is_array($actionFailureData) ? $actionFailureData : [];

        return new Machine(
            $this->getNullableNonEmptyString($data, 'state_category'),
            $this->getNullableNonEmptyString($data, 'ip_address'),
            $this->createMachineActionFailure($actionFailureData)
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
            if (is_string($componentName) && '' !== $componentName && is_array($componentData)) {
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

    /**
     * @param array<mixed> $data
     *
     * @return ServiceRequest[]
     *
     * @throws IncompleteDataException
     */
    private function createServiceRequests(array $data): array
    {
        $serviceRequests = [];

        foreach ($data as $serviceRequestIndex => $serviceRequestData) {
            if (is_array($serviceRequestData)) {
                try {
                    $serviceRequests[] = $this->createServiceRequest($serviceRequestData);
                } catch (IncompleteDataException $e) {
                    throw new IncompleteDataException($data, $serviceRequestIndex . '.' . $e->missingKey);
                }
            }
        }

        return $serviceRequests;
    }

    /**
     * @param array<mixed> $data
     *
     * @throws IncompleteDataException
     */
    private function createServiceRequest(array $data): ServiceRequest
    {
        $type = $this->getNonEmptyString($data, 'type');

        $attemptsData = $data['attempts'] ?? null;
        $attemptsData = is_array($attemptsData) ? $attemptsData : null;
        if (null === $attemptsData) {
            throw new IncompleteDataException($data, 'attempts');
        }

        try {
            $attempts = $this->createServiceRequestAttempts($attemptsData);
        } catch (IncompleteDataException $e) {
            throw new IncompleteDataException($data, 'attempts.' . $e->missingKey);
        }

        return new ServiceRequest($type, $attempts);
    }

    /**
     * @param array<mixed> $data
     *
     * @return ServiceRequestAttempt[]
     *
     * @throws IncompleteDataException
     */
    private function createServiceRequestAttempts(array $data): array
    {
        $attempts = [];

        foreach ($data as $attemptData) {
            if (is_array($attemptData)) {
                $attempts[] = new ServiceRequestAttempt($this->getNonEmptyString($attemptData, 'state'));
            }
        }

        return $attempts;
    }
}
