<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Factory\JobCoordinator;

use SmartAssert\ApiClient\Data\JobCoordinator\Job\Job;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\Preparation;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\ResultsJob;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\SerializedSuite;
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

        return new Job($id, $suiteId, $maximumDurationInSeconds, $preparation, $resultsJob, $serializedSuite);
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
}
