<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient;

use SmartAssert\ApiClient\Exception\Error\ErrorException;
use SmartAssert\ApiClient\Exception\File\NotFoundException as FileNotFoundException;
use SmartAssert\ApiClient\Exception\Http\HttpClientException;
use SmartAssert\ApiClient\Exception\Http\HttpException;
use SmartAssert\ApiClient\Exception\Http\NotFoundException;
use SmartAssert\ApiClient\Exception\Http\UnauthorizedException;
use SmartAssert\ApiClient\RequestBuilder\AcceptableContentTypesHeader;
use SmartAssert\ApiClient\RequestBuilder\ApiKeyAuthorizationHeader;
use SmartAssert\ApiClient\RequestBuilder\HeaderCollection;
use SmartAssert\ApiClient\RequestBuilder\RequestBuilder;
use SmartAssert\ApiClient\RequestBuilder\RouteRequirements;
use SmartAssert\ApiClient\ServiceClient\HttpHandler;

readonly class FileClient
{
    public function __construct(
        private HttpHandler $httpHandler,
        private RequestBuilder $requestBuilder,
    ) {
    }

    /**
     * @param non-empty-string $apiKey
     * @param non-empty-string $sourceId
     * @param non-empty-string $filename
     *
     * @throws ErrorException
     * @throws HttpClientException
     * @throws HttpException
     * @throws NotFoundException
     * @throws UnauthorizedException
     */
    public function create(string $apiKey, string $sourceId, string $filename, string $content): void
    {
        $request = $this->requestBuilder
            ->create(
                'POST',
                $this->createRouteRequirements($sourceId, $filename),
                new ApiKeyAuthorizationHeader($apiKey),
            )
            ->withBody('application/yaml', $content)
            ->get()
        ;

        $this->httpHandler->sendRequest($request);
    }

    /**
     * @param non-empty-string $apiKey
     * @param non-empty-string $sourceId
     * @param non-empty-string $filename
     *
     * @throws FileNotFoundException
     * @throws HttpClientException
     * @throws HttpException
     * @throws ErrorException
     */
    public function read(string $apiKey, string $sourceId, string $filename): string
    {
        $request = $this->requestBuilder
            ->create(
                'GET',
                $this->createRouteRequirements($sourceId, $filename),
                new HeaderCollection([
                    new ApiKeyAuthorizationHeader($apiKey),
                    new AcceptableContentTypesHeader(['application/yaml', 'text/x-yaml'])
                ]),
            )
            ->get()
        ;

        try {
            $response = $this->httpHandler->sendRequest($request);
        } catch (NotFoundException | UnauthorizedException) {
            throw new FileNotFoundException($filename);
        }

        return $response->getBody()->getContents();
    }

    /**
     * @param non-empty-string $apiKey
     * @param non-empty-string $sourceId
     * @param non-empty-string $filename
     *
     * @throws FileNotFoundException
     * @throws HttpClientException
     * @throws HttpException
     * @throws ErrorException
     */
    public function update(string $apiKey, string $sourceId, string $filename, string $content): void
    {
        $request = $this->requestBuilder
            ->create(
                'PUT',
                $this->createRouteRequirements($sourceId, $filename),
                new ApiKeyAuthorizationHeader($apiKey),
            )
            ->withBody('application/yaml', $content)
            ->get()
        ;

        try {
            $this->httpHandler->sendRequest($request);
        } catch (NotFoundException | UnauthorizedException) {
            throw new FileNotFoundException($filename);
        }
    }

    /**
     * @param non-empty-string $apiKey
     * @param non-empty-string $sourceId
     * @param non-empty-string $filename
     *
     * @throws FileNotFoundException
     * @throws HttpClientException
     * @throws HttpException
     * @throws ErrorException
     */
    public function delete(string $apiKey, string $sourceId, string $filename): void
    {
        $request = $this->requestBuilder
            ->create(
                'DELETE',
                $this->createRouteRequirements($sourceId, $filename),
                new ApiKeyAuthorizationHeader($apiKey),
            )
            ->get()
        ;

        try {
            $this->httpHandler->sendRequest($request);
        } catch (NotFoundException | UnauthorizedException) {
            throw new FileNotFoundException($filename);
        }
    }

    private function createRouteRequirements(string $sourceId, string $filename): RouteRequirements
    {
        return new RouteRequirements(
            'file-source-file',
            [
                'sourceId' => $sourceId,
                'filename' => $filename
            ]
        );
    }
}
