<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient;

use SmartAssert\ApiClient\Data\JobCoordinator\Job\Job;
use SmartAssert\ApiClient\Exception\ClientException;
use SmartAssert\ApiClient\Exception\IncompleteDataException;
use SmartAssert\ApiClient\Factory\JobCoordinator\JobFactory;
use SmartAssert\ApiClient\Request\Body\FormBody;
use SmartAssert\ApiClient\Request\Header\ApiKeyAuthorizationHeader;
use SmartAssert\ApiClient\Request\RequestSpecification;
use SmartAssert\ApiClient\Request\RouteRequirements;
use SmartAssert\ApiClient\ServiceClient\HttpHandler;

readonly class JobCoordinatorClient
{
    public function __construct(
        private JobFactory $jobFactory,
        private HttpHandler $httpHandler,
    ) {
    }

    /**
     * @param non-empty-string $apiKey
     * @param non-empty-string $suiteId
     *
     * @throws ClientException
     */
    public function create(string $apiKey, string $suiteId, int $maximumDurationInSeconds): Job
    {
        $requestSpecification = new RequestSpecification(
            'POST',
            new RouteRequirements('job-coordinator-job', ['entityId' => $suiteId]),
            new ApiKeyAuthorizationHeader($apiKey),
            new FormBody(['maximum_duration_in_seconds' => $maximumDurationInSeconds]),
        );

        try {
            return $this->jobFactory->create(
                $this->httpHandler->getJson($requestSpecification)
            );
        } catch (IncompleteDataException $e) {
            throw new ClientException($requestSpecification->getName(), $e);
        }
    }

    /**
     * @param non-empty-string $apiKey
     * @param non-empty-string $jobId
     *
     * @throws ClientException
     */
    public function get(string $apiKey, string $jobId): Job
    {
        $requestSpecification = new RequestSpecification(
            'GET',
            new RouteRequirements('job-coordinator-job', ['entityId' => $jobId]),
            new ApiKeyAuthorizationHeader($apiKey),
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
