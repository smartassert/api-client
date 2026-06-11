<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient;

use SmartAssert\ApiClient\Data\Source\SourceInterface;
use SmartAssert\ApiClient\Exception\Error\ErrorException;
use SmartAssert\ApiClient\Exception\Factory\IncompleteDataException;
use SmartAssert\ApiClient\Exception\ForbiddenException;
use SmartAssert\ApiClient\Exception\Http\HttpException;
use SmartAssert\ApiClient\Exception\Http\UnexpectedResponseFormatException;
use SmartAssert\ApiClient\Exception\HttpClientException;
use SmartAssert\ApiClient\Exception\IncompleteResponseDataException;
use SmartAssert\ApiClient\Exception\NotFoundException;
use SmartAssert\ApiClient\Exception\UnauthorizedException;
use SmartAssert\ApiClient\Factory\Source\SourceFactory;
use SmartAssert\ApiClient\Request\Header\ApiKeyAuthorizationHeader;
use SmartAssert\ApiClient\Request\RequestSpecification;
use SmartAssert\ApiClient\Request\RouteRequirements;
use SmartAssert\ApiClient\ServiceClient\HttpHandler;

readonly class SourceClient
{
    public function __construct(
        private SourceFactory $sourceFactory,
        private HttpHandler $httpHandler,
    ) {}

    /**
     * @param non-empty-string $apiKey
     *
     * @return SourceInterface[]
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
    public function list(string $apiKey): array
    {
        $requestSpecification = new RequestSpecification(
            'GET',
            new RouteRequirements('sources'),
            new ApiKeyAuthorizationHeader($apiKey),
        );

        $data = $this->httpHandler->getJson($requestSpecification);

        $sources = [];
        foreach ($data as $dataIndex => $sourceData) {
            if (is_array($sourceData)) {
                try {
                    $source = $this->sourceFactory->create($sourceData);
                } catch (IncompleteDataException $e) {
                    throw new IncompleteResponseDataException(
                        $requestSpecification,
                        new IncompleteDataException($data, $dataIndex . '.' . $e->missingKey)
                    );
                }

                if ($source instanceof SourceInterface) {
                    $sources[] = $source;
                }
            }
        }

        return $sources;
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
    public function get(string $apiKey, string $id): ?SourceInterface
    {
        return $this->doSourceAction('GET', $apiKey, $id);
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
    public function delete(string $apiKey, string $id): ?SourceInterface
    {
        return $this->doSourceAction('DELETE', $apiKey, $id);
    }

    /**
     * @param non-empty-string $method
     * @param non-empty-string $apiKey
     * @param non-empty-string $id
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
    private function doSourceAction(string $method, string $apiKey, string $id): ?SourceInterface
    {
        $requestSpecification = new RequestSpecification(
            $method,
            new RouteRequirements('source', ['sourceId' => $id]),
            new ApiKeyAuthorizationHeader($apiKey),
        );

        try {
            return $this->sourceFactory->create(
                $this->httpHandler->getJson($requestSpecification)
            );
        } catch (IncompleteDataException $e) {
            throw new IncompleteResponseDataException($requestSpecification, $e);
        }
    }
}
