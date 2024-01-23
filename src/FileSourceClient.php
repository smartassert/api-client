<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient;

use SmartAssert\ApiClient\Data\Source\FileSource;
use SmartAssert\ApiClient\Exception\Error\ErrorException;
use SmartAssert\ApiClient\Exception\Http\HttpClientException;
use SmartAssert\ApiClient\Exception\Http\HttpException;
use SmartAssert\ApiClient\Exception\Http\NotFoundException;
use SmartAssert\ApiClient\Exception\Http\UnauthorizedException;
use SmartAssert\ApiClient\Exception\Http\UnexpectedContentTypeException;
use SmartAssert\ApiClient\Exception\Http\UnexpectedDataException;
use SmartAssert\ApiClient\Exception\IncompleteDataException;
use SmartAssert\ApiClient\Factory\Source\SourceFactory;
use SmartAssert\ApiClient\RequestBuilder\ApiKeyAuthorizationHeader;
use SmartAssert\ApiClient\RequestBuilder\FormBody;
use SmartAssert\ApiClient\RequestBuilder\RequestBuilder;
use SmartAssert\ApiClient\RequestBuilder\RequestSpecification;
use SmartAssert\ApiClient\RequestBuilder\RouteRequirements;
use SmartAssert\ApiClient\ServiceClient\HttpHandler;

readonly class FileSourceClient
{
    public function __construct(
        private SourceFactory $sourceFactory,
        private HttpHandler $httpHandler,
        private RequestBuilder $requestBuilder,
    ) {
    }

    /**
     * @param non-empty-string $apiKey
     *
     * @throws HttpClientException
     * @throws HttpException
     * @throws IncompleteDataException
     * @throws NotFoundException
     * @throws UnauthorizedException
     * @throws UnexpectedContentTypeException
     * @throws UnexpectedDataException
     * @throws ErrorException
     */
    public function create(string $apiKey, string $label): FileSource
    {
        $request = $this->requestBuilder
            ->create(new RequestSpecification(
                'POST',
                new RouteRequirements('file-source'),
                new ApiKeyAuthorizationHeader($apiKey),
                new FormBody(['label' => $label])
            ))
            ->get()
        ;

        return $this->sourceFactory->createFileSource($this->httpHandler->getJson($request));
    }

    /**
     * @param non-empty-string $apiKey
     * @param non-empty-string $id
     *
     * @throws HttpClientException
     * @throws HttpException
     * @throws IncompleteDataException
     * @throws NotFoundException
     * @throws UnauthorizedException
     * @throws UnexpectedContentTypeException
     * @throws UnexpectedDataException
     * @throws ErrorException
     */
    public function update(string $apiKey, string $id, string $label): FileSource
    {
        $request = $this->requestBuilder
            ->create(new RequestSpecification(
                'PUT',
                new RouteRequirements('file-source', ['sourceId' => $id]),
                new ApiKeyAuthorizationHeader($apiKey),
                new FormBody(['label' => $label])
            ))
            ->get()
        ;

        return $this->sourceFactory->createFileSource($this->httpHandler->getJson($request));
    }

    /**
     * @param non-empty-string $apiKey
     * @param non-empty-string $id
     *
     * @return non-empty-string[]
     *
     * @throws HttpClientException
     * @throws HttpException
     * @throws NotFoundException
     * @throws UnauthorizedException
     * @throws UnexpectedContentTypeException
     * @throws UnexpectedDataException
     * @throws ErrorException
     */
    public function list(string $apiKey, string $id): array
    {
        $request = $this->requestBuilder
            ->create(new RequestSpecification(
                'GET',
                new RouteRequirements('file-source-list', ['sourceId' => $id]),
                new ApiKeyAuthorizationHeader($apiKey),
            ))
            ->get()
        ;

        $data = $this->httpHandler->getJson($request);

        $filenames = [];
        foreach ($data as $filename) {
            if (is_string($filename) && '' !== $filename) {
                $filenames[] = $filename;
            }
        }

        return $filenames;
    }
}
