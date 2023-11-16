<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Client\RequestExceptionInterface;
use SmartAssert\ApiClient\Model\Source\GitSource;
use SmartAssert\ArrayInspector\ArrayInspector;
use SmartAssert\ServiceClient\Authentication\BearerAuthentication;
use SmartAssert\ServiceClient\Client as ServiceClient;
use SmartAssert\ServiceClient\Exception\CurlExceptionInterface;
use SmartAssert\ServiceClient\Exception\InvalidModelDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseDataException;
use SmartAssert\ServiceClient\Exception\InvalidResponseTypeException;
use SmartAssert\ServiceClient\Exception\NonSuccessResponseException;
use SmartAssert\ServiceClient\Exception\UnauthorizedException;
use SmartAssert\ServiceClient\Payload\UrlEncodedPayload;
use SmartAssert\ServiceClient\Request;
use SmartAssert\ServiceClient\Response\JsonResponse;
use SmartAssert\ServiceClient\Response\ResponseInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

readonly class GitSourceClient
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private ServiceClient $serviceClient,
    ) {
    }

    /**
     * @param non-empty-string $apiKey
     *
     * @throws ClientExceptionInterface
     * @throws NetworkExceptionInterface
     * @throws CurlExceptionInterface
     * @throws RequestExceptionInterface
     * @throws NonSuccessResponseException
     * @throws InvalidResponseTypeException
     * @throws InvalidResponseDataException
     * @throws InvalidModelDataException
     * @throws UnauthorizedException
     */
    public function create(
        string $apiKey,
        string $label,
        string $hostUrl,
        string $path,
        ?string $credentials,
    ): GitSource {
        $response = $this->serviceClient->sendRequest(
            (new Request('POST', $this->urlGenerator->generate('git-source_create')))
                ->withAuthentication(
                    new BearerAuthentication($apiKey)
                )
                ->withPayload(
                    new UrlEncodedPayload([
                        'label' => $label,
                        'host-url' => $hostUrl,
                        'path' => $path,
                        'credentials' => $credentials,
                    ])
                )
        );

        return $this->handleGitSourceResponse($response);
    }

    /**
     * @param non-empty-string $apiKey
     * @param non-empty-string $id
     *
     * @throws ClientExceptionInterface
     * @throws NetworkExceptionInterface
     * @throws CurlExceptionInterface
     * @throws RequestExceptionInterface
     * @throws NonSuccessResponseException
     * @throws InvalidResponseTypeException
     * @throws InvalidResponseDataException
     * @throws InvalidModelDataException
     * @throws UnauthorizedException
     */
    public function get(string $apiKey, string $id): GitSource
    {
        return $this->handleRequest($apiKey, 'GET', $id);
    }

    /**
     * @param non-empty-string  $apiKey
     * @param non-empty-string  $id
     * @param non-empty-string  $label
     * @param non-empty-string  $hostUrl
     * @param non-empty-string  $path
     * @param ?non-empty-string $credentials
     *
     * @throws ClientExceptionInterface
     * @throws NetworkExceptionInterface
     * @throws CurlExceptionInterface
     * @throws RequestExceptionInterface
     * @throws NonSuccessResponseException
     * @throws InvalidResponseTypeException
     * @throws InvalidResponseDataException
     * @throws InvalidModelDataException
     * @throws UnauthorizedException
     */
    public function update(
        string $apiKey,
        string $id,
        string $label,
        string $hostUrl,
        string $path,
        ?string $credentials,
    ): GitSource {
        return $this->handleRequest($apiKey, 'PUT', $id, $label, $hostUrl, $path, $credentials);
    }

    /**
     * @param non-empty-string $apiKey
     * @param non-empty-string $id
     *
     * @throws ClientExceptionInterface
     * @throws NetworkExceptionInterface
     * @throws CurlExceptionInterface
     * @throws RequestExceptionInterface
     * @throws NonSuccessResponseException
     * @throws InvalidResponseTypeException
     * @throws InvalidResponseDataException
     * @throws InvalidModelDataException
     * @throws UnauthorizedException
     */
    public function delete(string $apiKey, string $id): GitSource
    {
        return $this->handleRequest($apiKey, 'DELETE', $id);
    }

    /**
     * @param non-empty-string  $apiKey
     * @param non-empty-string  $method
     * @param non-empty-string  $id
     * @param ?non-empty-string $label
     * @param ?non-empty-string $hostUrl
     * @param ?non-empty-string $path
     * @param ?non-empty-string $credentials
     *
     * @throws ClientExceptionInterface
     * @throws CurlExceptionInterface
     * @throws InvalidModelDataException
     * @throws InvalidResponseDataException
     * @throws InvalidResponseTypeException
     * @throws NetworkExceptionInterface
     * @throws NonSuccessResponseException
     * @throws RequestExceptionInterface
     * @throws UnauthorizedException
     */
    private function handleRequest(
        string $apiKey,
        string $method,
        string $id,
        ?string $label = null,
        ?string $hostUrl = null,
        ?string $path = null,
        ?string $credentials = null,
    ): GitSource {
        $request = new Request($method, $this->urlGenerator->generate('git-source', ['sourceId' => $id]));
        $request = $request->withAuthentication(new BearerAuthentication($apiKey));

        $payload = [];

        if (null !== $label) {
            $payload['label'] = $label;
        }

        if (null !== $hostUrl) {
            $payload['host-url'] = $hostUrl;
        }

        if (null !== $path) {
            $payload['path'] = $path;
        }

        if (null !== $credentials) {
            $payload['credentials'] = $credentials;
        }

        if ([] !== $payload) {
            $request = $request->withPayload(new UrlEncodedPayload($payload));
        }

        $response = $this->serviceClient->sendRequest($request);

        return $this->handleGitSourceResponse($response);
    }

    /**
     * @throws InvalidModelDataException
     * @throws InvalidResponseDataException
     * @throws InvalidResponseTypeException
     * @throws NonSuccessResponseException
     */
    private function handleGitSourceResponse(ResponseInterface $response): GitSource
    {
        if (!$response->isSuccessful()) {
            throw new NonSuccessResponseException($response->getHttpResponse());
        }

        if (!$response instanceof JsonResponse) {
            throw InvalidResponseTypeException::create($response, JsonResponse::class);
        }

        $responseDataInspector = new ArrayInspector($response->getData());
        $modelData = $responseDataInspector->getArray('git_source');

        $modelDataInspector = new ArrayInspector($modelData);
        $id = $modelDataInspector->getNonEmptyString('id');
        $label = $modelDataInspector->getNonEmptyString('label');
        $hostUrl = $modelDataInspector->getNonEmptyString('host_url');
        $path = $modelDataInspector->getNonEmptyString('path');

        if (null === $id || null === $label || null === $hostUrl || null === $path) {
            throw InvalidModelDataException::fromJsonResponse(GitSource::class, $response);
        }

        $hasCredentials = $modelDataInspector->getBoolean('has_credentials');
        if (true !== $hasCredentials) {
            $hasCredentials = false;
        }

        $deletedAt = $modelDataInspector->getPositiveInteger('deleted_at');

        return new GitSource($id, $label, $hostUrl, $path, $hasCredentials, $deletedAt);
    }
}
