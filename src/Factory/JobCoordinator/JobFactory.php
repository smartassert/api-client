<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Factory\JobCoordinator;

use SmartAssert\ApiClient\Data\JobCoordinator\Job\Components;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\Job;
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
        private MetaStateFactory $metaStateFactory,
        private ResultsJobFactory $resultsJobFactory,
        private PreparationFactory $preparationFactory,
        private SerializedSuiteFactory $serializedSuiteFactory,
        private MachineFactory $machineFactory,
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
            $preparation = $this->preparationFactory->create($preparationData);
        } catch (IncompleteDataException $e) {
            throw new IncompleteDataException($data, 'preparation.' . $e->missingKey);
        }

        if (!array_key_exists('components', $data)) {
            throw new IncompleteDataException($data, 'components');
        }

        $componentsData = $data['components'] ?? [];
        $componentsData = is_array($componentsData) ? $componentsData : [];

        if (!array_key_exists('results-job', $componentsData)) {
            throw new IncompleteDataException($data, 'components.results-job');
        }

        $resultsJob = $this->resultsJobFactory->create($componentsData['results-job'] ?? []);

        if (!array_key_exists('serialized-suite', $componentsData)) {
            throw new IncompleteDataException($data, 'components.serialized-suite');
        }

        $serializedSuite = $this->serializedSuiteFactory->create($componentsData['serialized-suite'] ?? []);

        if (!array_key_exists('machine', $componentsData)) {
            throw new IncompleteDataException($data, 'components.machine');
        }

        $machine = $this->machineFactory->create($componentsData['machine'] ?? []);

        $workerJobData = $componentsData['worker-job'] ?? null;
        if (!is_array($workerJobData)) {
            throw new IncompleteDataException($data, 'components.worker-job');
        }

        try {
            $workerJob = $this->createWorkerJob($workerJobData);
        } catch (IncompleteDataException $e) {
            throw new IncompleteDataException($data, 'worker-job.' . $e->missingKey);
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

        $components = [];
        if (null !== $resultsJob) {
            $components['results-job'] = $resultsJob;
        }

        if (null !== $serializedSuite) {
            $components['serialized-suite'] = $serializedSuite;
        }

        if (null !== $machine) {
            $components['machine'] = $machine;
        }

        $components['worker-job'] = $workerJob;

        return new Job(
            $summary,
            $preparation,
            $this->metaStateFactory->create($data),
            new Components($components),
            $serviceRequests,
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
            if (is_string($componentName) && '' !== $componentName && is_array($componentData)) {
                try {
                    $components[$componentName] = new WorkerJobComponent(
                        $this->getNonEmptyString($componentData, 'state'),
                        $this->metaStateFactory->create($componentData),
                    );
                } catch (IncompleteDataException $e) {
                    throw new IncompleteDataException($data, 'components.' . $componentName . '.' . $e->missingKey);
                }
            }
        }

        return new WorkerJob($state, $this->metaStateFactory->create($data), $components);
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
