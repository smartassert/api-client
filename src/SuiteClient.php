<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient;

use SmartAssert\ApiClient\Data\Source\Suite;
use SmartAssert\ApiClient\Exception\Error\ErrorException;
use SmartAssert\ApiClient\Exception\Factory\IncompleteDataException;
use SmartAssert\ApiClient\Exception\ForbiddenException;
use SmartAssert\ApiClient\Exception\Http\HttpException;
use SmartAssert\ApiClient\Exception\Http\UnexpectedResponseFormatException;
use SmartAssert\ApiClient\Exception\HttpClientException;
use SmartAssert\ApiClient\Exception\IncompleteResponseDataException;
use SmartAssert\ApiClient\Exception\NotFoundException;
use SmartAssert\ApiClient\Exception\UnauthorizedException;
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
     * @throws ErrorException
     * @throws ForbiddenException
     * @throws HttpClientException
     * @throws HttpException
     * @throws IncompleteResponseDataException
     * @throws NotFoundException
     * @throws UnauthorizedException
     * @throws UnexpectedResponseFormatException
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
     * @throws ErrorException
     * @throws ForbiddenException
     * @throws HttpClientException
     * @throws HttpException
     * @throws IncompleteResponseDataException
     * @throws NotFoundException
     * @throws UnauthorizedException
     * @throws UnexpectedResponseFormatException
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
     * @throws ErrorException
     * @throws ForbiddenException
     * @throws HttpClientException
     * @throws HttpException
     * @throws IncompleteResponseDataException
     * @throws NotFoundException
     * @throws UnauthorizedException
     * @throws UnexpectedResponseFormatException
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
     * @throws ErrorException
     * @throws ForbiddenException
     * @throws HttpClientException
     * @throws HttpException
     * @throws IncompleteResponseDataException
     * @throws NotFoundException
     * @throws UnauthorizedException
     * @throws UnexpectedResponseFormatException
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
     * @throws ErrorException
     * @throws ForbiddenException
     * @throws HttpClientException
     * @throws HttpException
     * @throws NotFoundException
     * @throws UnauthorizedException
     * @throws IncompleteResponseDataException
     * @throws UnexpectedResponseFormatException
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
