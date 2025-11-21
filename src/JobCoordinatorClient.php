<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient;

use SmartAssert\ApiClient\Data\JobCoordinator\Job\Job;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\Summary;
use SmartAssert\ApiClient\Exception\ClientException;
use SmartAssert\ApiClient\Exception\IncompleteDataException;
use SmartAssert\ApiClient\Factory\JobCoordinator\JobFactory;
use SmartAssert\ApiClient\Factory\JobCoordinator\SummaryFactory;
use SmartAssert\ApiClient\Request\Body\BodyInterface;
use SmartAssert\ApiClient\Request\Body\FormBody;
use SmartAssert\ApiClient\Request\Header\ApiKeyAuthorizationHeader;
use SmartAssert\ApiClient\Request\RequestSpecification;
use SmartAssert\ApiClient\Request\RouteRequirements;
use SmartAssert\ApiClient\ServiceClient\HttpHandler;

readonly class JobCoordinatorClient
{
    public function __construct(
        private JobFactory $jobFactory,
        private SummaryFactory $summaryFactory,
        private HttpHandler $httpHandler,
    ) {}

    /**
     * @param non-empty-string $apiKey
     * @param non-empty-string $suiteId
     *
     * @throws ClientException
     */
    public function create(string $apiKey, string $suiteId, int $maximumDurationInSeconds): Job
    {
        return $this->doJobAction(
            'POST',
            $apiKey,
            $suiteId,
            new FormBody(['maximum_duration_in_seconds' => $maximumDurationInSeconds])
        );
    }

    /**
     * @param non-empty-string $apiKey
     * @param non-empty-string $jobId
     *
     * @throws ClientException
     */
    public function get(string $apiKey, string $jobId): Job
    {
        return $this->doJobAction('GET', $apiKey, $jobId);
    }

    /**
     * @param non-empty-string $apiKey
     * @param non-empty-string $suiteId
     *
     * @return Summary[]
     *
     * @throws ClientException
     */
    public function list(string $apiKey, string $suiteId): array
    {
        $requestSpecification = new RequestSpecification(
            'GET',
            new RouteRequirements('job-coordinator-list', ['suiteId' => $suiteId]),
            new ApiKeyAuthorizationHeader($apiKey)
        );

        $jobSummaries = [];
        $jobSummaryDataCollection = $this->httpHandler->getJson($requestSpecification);

        foreach ($jobSummaryDataCollection as $jobSummaryIndex => $jobSummaryData) {
            if (is_array($jobSummaryData)) {
                try {
                    $jobSummaries[] = $this->summaryFactory->create($jobSummaryData);
                } catch (IncompleteDataException $e) {
                    throw new ClientException(
                        $requestSpecification->getName(),
                        new IncompleteDataException(
                            $jobSummaryDataCollection,
                            $jobSummaryIndex . '.' . $e->missingKey
                        )
                    );
                }
            }
        }

        return $jobSummaries;
    }

    /**
     * @param non-empty-string $method
     * @param non-empty-string $apiKey
     * @param non-empty-string $entityId
     *
     * @throws ClientException
     */
    private function doJobAction(string $method, string $apiKey, string $entityId, ?BodyInterface $body = null): Job
    {
        $requestSpecification = new RequestSpecification(
            $method,
            new RouteRequirements('job-coordinator-job', ['entityId' => $entityId]),
            new ApiKeyAuthorizationHeader($apiKey),
            $body
        );

        try {
            return $this->jobFactory->create(
                $this->httpHandler->getJson($requestSpecification)
            );
        } catch (IncompleteDataException $e) {
            throw new ClientException($requestSpecification->getName(), $e);
        }
    }
}
