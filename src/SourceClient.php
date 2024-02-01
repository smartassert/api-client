<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient;

use SmartAssert\ApiClient\Data\Source\SourceInterface;
use SmartAssert\ApiClient\Exception\Error\ErrorException;
use SmartAssert\ApiClient\Exception\Http\FailedRequestException;
use SmartAssert\ApiClient\Exception\Http\HttpException;
use SmartAssert\ApiClient\Exception\Http\NotFoundException;
use SmartAssert\ApiClient\Exception\Http\UnauthorizedException;
use SmartAssert\ApiClient\Exception\Http\UnexpectedContentTypeException;
use SmartAssert\ApiClient\Exception\Http\UnexpectedDataException;
use SmartAssert\ApiClient\Exception\IncompleteDataException;
use SmartAssert\ApiClient\Exception\IncompleteResponseDataException;
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
    ) {
    }

    /**
     * @param non-empty-string $apiKey
     *
     * @return SourceInterface[]
     *
     * @throws FailedRequestException
     * @throws HttpException
     * @throws IncompleteResponseDataException
     * @throws NotFoundException
     * @throws UnauthorizedException
     * @throws UnexpectedContentTypeException
     * @throws UnexpectedDataException
     * @throws ErrorException
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
        foreach ($data as $sourceData) {
            if (is_array($sourceData)) {
                try {
                    $source = $this->sourceFactory->create($sourceData);
                } catch (IncompleteDataException $e) {
                    throw new IncompleteResponseDataException($requestSpecification->getName(), $e);
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
     * @throws FailedRequestException
     * @throws HttpException
     * @throws IncompleteResponseDataException
     * @throws NotFoundException
     * @throws UnauthorizedException
     * @throws UnexpectedContentTypeException
     * @throws UnexpectedDataException
     * @throws ErrorException
     */
    public function get(string $apiKey, string $id): ?SourceInterface
    {
        return $this->doSourceAction('GET', $apiKey, $id);
    }

    /**
     * @param non-empty-string $apiKey
     * @param non-empty-string $id
     *
     * @throws FailedRequestException
     * @throws HttpException
     * @throws IncompleteResponseDataException
     * @throws NotFoundException
     * @throws UnauthorizedException
     * @throws UnexpectedContentTypeException
     * @throws UnexpectedDataException
     * @throws ErrorException
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
     * @throws FailedRequestException
     * @throws HttpException
     * @throws IncompleteResponseDataException
     * @throws NotFoundException
     * @throws UnauthorizedException
     * @throws UnexpectedContentTypeException
     * @throws UnexpectedDataException
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
            throw new IncompleteResponseDataException($requestSpecification->getName(), $e);
        }
    }
}
