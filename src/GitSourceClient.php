<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient;

use SmartAssert\ApiClient\Data\Source\GitSource;
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
use SmartAssert\ApiClient\RequestBuilder\RequestBuilder;
use SmartAssert\ApiClient\RequestBuilder\RouteRequirements;
use SmartAssert\ApiClient\ServiceClient\HttpHandler;

readonly class GitSourceClient
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
    public function create(
        string $apiKey,
        string $label,
        string $hostUrl,
        string $path,
        ?string $credentials,
    ): GitSource {
        return $this->doAction('POST', $apiKey, $label, $hostUrl, $path, $credentials);
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
    public function update(
        string $apiKey,
        string $id,
        string $label,
        string $hostUrl,
        string $path,
        ?string $credentials,
    ): GitSource {
        return $this->doAction('PUT', $apiKey, $label, $hostUrl, $path, $credentials, $id);
    }

    /**
     * @throws HttpClientException
     * @throws HttpException
     * @throws IncompleteDataException
     * @throws NotFoundException
     * @throws UnauthorizedException
     * @throws UnexpectedContentTypeException
     * @throws UnexpectedDataException
     * @throws ErrorException
     */
    private function doAction(
        string $method,
        string $apiKey,
        string $label,
        string $hostUrl,
        string $path,
        ?string $credentials,
        ?string $id = null,
    ): GitSource {
        $request = $this->requestBuilder
            ->create(
                $method,
                new RouteRequirements('git-source', ['sourceId' => $id]),
                new ApiKeyAuthorizationHeader($apiKey),
            )
            ->withFormBody([
                'label' => $label,
                'host-url' => $hostUrl,
                'path' => $path,
                'credentials' => $credentials,
            ])
            ->get()
        ;

        return $this->sourceFactory->createGitSource($this->httpHandler->getJson($request));
    }
}
