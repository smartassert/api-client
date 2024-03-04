<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient;

use SmartAssert\ApiClient\Data\Source\SerializedSuite;
use SmartAssert\ApiClient\Exception\ClientException;
use SmartAssert\ApiClient\Exception\IncompleteDataException;
use SmartAssert\ApiClient\Factory\Source\SerializedSuiteFactory;
use SmartAssert\ApiClient\Request\Body\FormBody;
use SmartAssert\ApiClient\Request\Header\ApiKeyAuthorizationHeader;
use SmartAssert\ApiClient\Request\RequestSpecification;
use SmartAssert\ApiClient\Request\RouteRequirements;
use SmartAssert\ApiClient\ServiceClient\HttpHandler;

readonly class SerializedSuiteClient
{
    public function __construct(
        private SerializedSuiteFactory $serializedSuiteFactory,
        private HttpHandler $httpHandler,
    ) {
    }

    /**
     * @param non-empty-string      $apiKey
     * @param non-empty-string      $suiteId
     * @param non-empty-string      $serializedSuiteId
     * @param array<string, scalar> $parameters
     *
     * @throws ClientException
     */
    public function create(
        string $apiKey,
        string $suiteId,
        string $serializedSuiteId,
        array $parameters
    ): SerializedSuite {
        $requestSpecification = new RequestSpecification(
            'POST',
            new RouteRequirements(
                'serialized-suite-create',
                [
                    'suiteId' => $suiteId,
                    'serializedSuiteId' => $serializedSuiteId
                ],
            ),
            new ApiKeyAuthorizationHeader($apiKey),
            new FormBody($parameters)
        );

        try {
            return $this->serializedSuiteFactory->create(
                $this->httpHandler->getJson($requestSpecification)
            );
        } catch (IncompleteDataException $e) {
            throw new ClientException($requestSpecification->getName(), $e);
        }
    }
}
