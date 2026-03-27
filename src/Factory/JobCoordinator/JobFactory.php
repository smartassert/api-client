<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Factory\JobCoordinator;

use SmartAssert\ApiClient\Data\JobCoordinator\Job\Components;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\Job;
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
        private WorkerJobFactory $workerJobFactory,
        private ServiceRequestFactory $serviceRequestFactory,
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
            $workerJob = $this->workerJobFactory->create($workerJobData);
        } catch (IncompleteDataException $e) {
            throw new IncompleteDataException($data, 'worker-job.' . $e->missingKey);
        }

        $serviceRequestDataCollection = $data['service_requests'] ?? null;
        if (!is_array($serviceRequestDataCollection)) {
            throw new IncompleteDataException($data, 'service_requests');
        }

        try {
            $serviceRequests = $this->serviceRequestFactory->createCollection($serviceRequestDataCollection);
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
}
