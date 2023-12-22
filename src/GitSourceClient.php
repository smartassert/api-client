<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient;

use GuzzleHttp\Psr7\Request as HttpRequest;
use SmartAssert\ApiClient\Data\Source\GitSource;
use SmartAssert\ApiClient\Exception\Http\HttpClientException;
use SmartAssert\ApiClient\Exception\Http\HttpException;
use SmartAssert\ApiClient\Exception\Http\NotFoundException;
use SmartAssert\ApiClient\Exception\Http\UnauthorizedException;
use SmartAssert\ApiClient\Exception\Http\UnexpectedContentTypeException;
use SmartAssert\ApiClient\Exception\Http\UnexpectedDataException;
use SmartAssert\ApiClient\Exception\IncompleteDataException;
use SmartAssert\ApiClient\Factory\Source\SourceFactory;
use SmartAssert\ApiClient\ServiceClient\HttpHandler;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

readonly class GitSourceClient
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private SourceFactory $sourceFactory,
        private HttpHandler $httpHandler,
    ) {
    }

    /**
     * @param non-empty-string  $apiKey
     * @param non-empty-string  $label
     * @param non-empty-string  $hostUrl
     * @param non-empty-string  $path
     * @param ?non-empty-string $credentials
     *
     * @throws HttpClientException
     * @throws HttpException
     * @throws IncompleteDataException
     * @throws NotFoundException
     * @throws UnauthorizedException
     * @throws UnexpectedContentTypeException
     * @throws UnexpectedDataException
     */
    public function create(
        string $apiKey,
        string $label,
        string $hostUrl,
        string $path,
        ?string $credentials,
    ): GitSource {
        return $this->handleRequest($apiKey, 'POST', $label, $hostUrl, $path, $credentials);
    }

    /**
     * @param non-empty-string  $apiKey
     * @param non-empty-string  $id
     * @param non-empty-string  $label
     * @param non-empty-string  $hostUrl
     * @param non-empty-string  $path
     * @param ?non-empty-string $credentials
     *
     * @throws HttpClientException
     * @throws HttpException
     * @throws IncompleteDataException
     * @throws NotFoundException
     * @throws UnauthorizedException
     * @throws UnexpectedContentTypeException
     * @throws UnexpectedDataException
     */
    public function update(
        string $apiKey,
        string $id,
        string $label,
        string $hostUrl,
        string $path,
        ?string $credentials,
    ): GitSource {
        return $this->handleRequest($apiKey, 'PUT', $label, $hostUrl, $path, $credentials, $id);
    }

    /**
     * @param non-empty-string  $apiKey
     * @param non-empty-string  $method
     * @param non-empty-string  $label
     * @param non-empty-string  $hostUrl
     * @param non-empty-string  $path
     * @param ?non-empty-string $credentials
     * @param ?non-empty-string $id
     *
     * @throws \SmartAssert\ApiClient\Exception\IncompleteDataException
     * @throws HttpClientException
     * @throws HttpException
     * @throws NotFoundException
     * @throws UnauthorizedException
     * @throws UnexpectedContentTypeException
     * @throws UnexpectedDataException
     */
    private function handleRequest(
        string $apiKey,
        string $method,
        string $label,
        string $hostUrl,
        string $path,
        ?string $credentials = null,
        ?string $id = null,
    ): GitSource {
        $payload = [
            'label' => $label,
            'host-url' => $hostUrl,
            'path' => $path,
        ];

        if (is_string($credentials)) {
            $payload['credentials'] = $credentials;
        }

        $request = new HttpRequest(
            $method,
            $this->urlGenerator->generate('git-source', ['sourceId' => $id]),
            [
                'authorization' => 'Bearer ' . $apiKey,
                'translate-authorization-to' => 'api-token',
                'content-type' => 'application/x-www-form-urlencoded',
            ],
            http_build_query($payload)
        );

        return $this->sourceFactory->createGitSource($this->httpHandler->getJson($request));
    }
}
