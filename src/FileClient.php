<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient;

use SmartAssert\ApiClient\Exception\ClientException;
use SmartAssert\ApiClient\Exception\File\NotFoundException as FileNotFoundException;
use SmartAssert\ApiClient\Exception\NotFoundException;
use SmartAssert\ApiClient\Exception\UnauthorizedException;
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
     *
     * @throws ClientException
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
     *
     * @throws ClientException
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
        } catch (ClientException $e) {
            $innerException = $e->getInnerException();

            if ($innerException instanceof NotFoundException || $innerException instanceof UnauthorizedException) {
                throw new ClientException($e->getRequestName(), new FileNotFoundException($filename));
            }

            throw $e;
        }

        return $response->getBody()->getContents();
    }

    /**
     * @param non-empty-string $apiKey
     * @param non-empty-string $sourceId
     *
     * @throws ClientException
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
        } catch (ClientException $e) {
            $innerException = $e->getInnerException();

            if ($innerException instanceof NotFoundException || $innerException instanceof UnauthorizedException) {
                throw new ClientException($e->getRequestName(), new FileNotFoundException($filename));
            }

            throw $e;
        }
    }

    /**
     * @param non-empty-string $apiKey
     * @param non-empty-string $sourceId
     *
     * @throws ClientException
     */
    public function delete(string $apiKey, string $sourceId, string $filename): void
    {
        try {
            $this->httpHandler->sendRequest(new RequestSpecification(
                'DELETE',
                $this->createRouteRequirements($sourceId, $filename),
                new ApiKeyAuthorizationHeader($apiKey),
            ));
        } catch (ClientException $e) {
            $innerException = $e->getInnerException();

            if ($innerException instanceof NotFoundException || $innerException instanceof UnauthorizedException) {
                throw new ClientException($e->getRequestName(), new FileNotFoundException($filename));
            }

            throw $e;
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
