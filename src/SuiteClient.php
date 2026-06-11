<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient;

use SmartAssert\ApiClient\Data\Source\Suite;
use SmartAssert\ApiClient\Exception\ClientExceptionInterface;
use SmartAssert\ApiClient\Exception\Factory\IncompleteDataException;
use SmartAssert\ApiClient\Exception\IncompleteResponseDataException;
use SmartAssert\ApiClient\Factory\Source\SuiteFactory;
use SmartAssert\ApiClient\Request\Body\FormBody;
use SmartAssert\ApiClient\Request\Header\ApiKeyAuthorizationHeader;
use SmartAssert\ApiClient\Request\RequestSpecification;
use SmartAssert\ApiClient\Request\RouteRequirements;
use SmartAssert\ApiClient\ServiceClient\HttpHandler;

readonly class SuiteClient
{
    public function __construct(
        private SuiteFactory $suiteFactory,
        private HttpHandler $httpHandler,
    ) {}

    /**
     * @param non-empty-string $apiKey
     * @param string[]         $tests
     *
     * @throws ClientExceptionInterface
     */
    public function create(string $apiKey, string $sourceId, string $label, array $tests): Suite
    {
        $requestSpecification = new RequestSpecification(
            'POST',
            new RouteRequirements('suite'),
            new ApiKeyAuthorizationHeader($apiKey),
            new FormBody([
                'source_id' => $sourceId,
                'label' => $label,
                'tests' => $tests,
            ]),
        );

        try {
            return $this->suiteFactory->create(
                $this->httpHandler->getJson($requestSpecification)
            );
        } catch (IncompleteDataException $e) {
            throw new IncompleteResponseDataException($requestSpecification, $e);
        }
    }

    /**
     * @param non-empty-string $apiKey
     * @param non-empty-string $id
     *
     * @throws ClientExceptionInterface
     */
    public function get(string $apiKey, string $id): Suite
    {
        $requestSpecification = new RequestSpecification(
            'GET',
            new RouteRequirements('suite', ['suiteId' => $id]),
            new ApiKeyAuthorizationHeader($apiKey),
        );

        try {
            return $this->suiteFactory->create(
                $this->httpHandler->getJson($requestSpecification)
            );
        } catch (IncompleteDataException $e) {
            throw new IncompleteResponseDataException($requestSpecification, $e);
        }
    }

    /**
     * @param non-empty-string $apiKey
     * @param string[]         $tests
     *
     * @throws ClientExceptionInterface
     */
    public function update(string $apiKey, string $id, string $sourceId, string $label, array $tests): Suite
    {
        $requestSpecification = new RequestSpecification(
            'PUT',
            new RouteRequirements('suite', ['suiteId' => $id]),
            new ApiKeyAuthorizationHeader($apiKey),
            new FormBody([
                'source_id' => $sourceId,
                'label' => $label,
                'tests' => $tests,
            ]),
        );

        try {
            return $this->suiteFactory->create(
                $this->httpHandler->getJson($requestSpecification)
            );
        } catch (IncompleteDataException $e) {
            throw new IncompleteResponseDataException($requestSpecification, $e);
        }
    }

    /**
     * @param non-empty-string $apiKey
     * @param non-empty-string $id
     *
     * @throws ClientExceptionInterface
     */
    public function delete(string $apiKey, string $id): Suite
    {
        $requestSpecification = new RequestSpecification(
            'DELETE',
            new RouteRequirements('suite', ['suiteId' => $id]),
            new ApiKeyAuthorizationHeader($apiKey),
        );

        try {
            return $this->suiteFactory->create(
                $this->httpHandler->getJson($requestSpecification)
            );
        } catch (IncompleteDataException $e) {
            throw new IncompleteResponseDataException($requestSpecification, $e);
        }
    }

    /**
     * @param non-empty-string $apiKey
     *
     * @return Suite[]
     *
     * @throws ClientExceptionInterface
     */
    public function list(string $apiKey): array
    {
        $requestSpecification = new RequestSpecification(
            'GET',
            new RouteRequirements('suites'),
            new ApiKeyAuthorizationHeader($apiKey),
        );

        $data = $this->httpHandler->getJson($requestSpecification);

        $suites = [];
        foreach ($data as $dataIndex => $suiteData) {
            if (is_array($suiteData)) {
                try {
                    $suites[] = $this->suiteFactory->create($suiteData);
                } catch (IncompleteDataException $e) {
                    throw new IncompleteResponseDataException(
                        $requestSpecification,
                        new IncompleteDataException($data, $dataIndex . '.' . $e->missingKey)
                    );
                }
            }
        }

        return $suites;
    }
}
