<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient;

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
use SmartAssert\ApiClient\ServiceClient\RequestBuilder;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

readonly class GitSourceClient
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private SourceFactory $sourceFactory,
        private HttpHandler $httpHandler,
        private RequestBuilder $requestBuilder,
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
        $request = $this->requestBuilder
            ->create('POST', $this->urlGenerator->generate('git-source'))
            ->withApiKeyAuthorization($apiKey)
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
        $request = $this->requestBuilder
            ->create('PUT', $this->urlGenerator->generate('git-source', ['sourceId' => $id]))
            ->withApiKeyAuthorization($apiKey)
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
