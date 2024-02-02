<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient;

use SmartAssert\ApiClient\Exception\Error\ErrorException;
use SmartAssert\ApiClient\Exception\File\NotFoundException as FileNotFoundException;
use SmartAssert\ApiClient\Exception\Http\HttpClientException;
use SmartAssert\ApiClient\Exception\Http\HttpException;
use SmartAssert\ApiClient\Exception\Http\NotFoundException;
use SmartAssert\ApiClient\Exception\Http\UnauthorizedException;
use SmartAssert\ApiClient\Request\Body\YamlBody;
use SmartAssert\ApiClient\Request\Header\AcceptableContentTypesHeader;
use SmartAssert\ApiClient\Request\Header\ApiKeyAuthorizationHeader;
use SmartAssert\ApiClient\Request\Header\HeaderCollection;
use SmartAssert\ApiClient\Request\RequestSpecification;
use SmartAssert\ApiClient\Request\RouteRequirements;
use SmartAssert\ApiClient\ServiceClient\HttpHandler;

readonly class FileClient
{
    public function __construct(
        private HttpHandler $httpHandler,
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
        $this->httpHandler->sendRequest(new RequestSpecification(
            'POST',
            $this->createRouteRequirements($sourceId, $filename),
            new ApiKeyAuthorizationHeader($apiKey),
            new YamlBody($content),
        ));
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
        try {
            $response = $this->httpHandler->sendRequest(new RequestSpecification(
                'GET',
                $this->createRouteRequirements($sourceId, $filename),
                new HeaderCollection([
                    new ApiKeyAuthorizationHeader($apiKey),
                    new AcceptableContentTypesHeader(['application/yaml', 'text/x-yaml'])
                ]),
            ));
        } catch (NotFoundException | UnauthorizedException $e) {
            throw new FileNotFoundException($e->getRequestName(), $filename);
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
        try {
            $this->httpHandler->sendRequest(new RequestSpecification(
                'PUT',
                $this->createRouteRequirements($sourceId, $filename),
                new ApiKeyAuthorizationHeader($apiKey),
                new YamlBody($content),
            ));
        } catch (NotFoundException | UnauthorizedException $e) {
            throw new FileNotFoundException($e->getRequestName(), $filename);
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
        try {
            $this->httpHandler->sendRequest(new RequestSpecification(
                'DELETE',
                $this->createRouteRequirements($sourceId, $filename),
                new ApiKeyAuthorizationHeader($apiKey),
            ));
        } catch (NotFoundException | UnauthorizedException $e) {
            throw new FileNotFoundException($e->getRequestName(), $filename);
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
