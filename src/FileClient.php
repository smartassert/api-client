<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Client\RequestExceptionInterface;
use SmartAssert\ApiClient\Exception\File\DuplicateFileException;
use SmartAssert\ApiClient\Exception\File\NotFoundException;
use SmartAssert\ServiceClient\Authentication\BearerAuthentication;
use SmartAssert\ServiceClient\Client as ServiceClient;
use SmartAssert\ServiceClient\Exception\CurlExceptionInterface;
use SmartAssert\ServiceClient\Exception\InvalidResponseDataException;
use SmartAssert\ServiceClient\Exception\NonSuccessResponseException;
use SmartAssert\ServiceClient\Exception\UnauthorizedException;
use SmartAssert\ServiceClient\Payload\Payload;
use SmartAssert\ServiceClient\Request;
use SmartAssert\ServiceClient\Response\JsonResponse;
use SmartAssert\ServiceClient\Response\ResponseInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

readonly class FileClient
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private ServiceClient $serviceClient,
    ) {
    }

    /**
     * @param non-empty-string $apiKey
     * @param non-empty-string $sourceId
     * @param non-empty-string $filename
     *
     * @throws ClientExceptionInterface
     * @throws NetworkExceptionInterface
     * @throws CurlExceptionInterface
     * @throws RequestExceptionInterface
     * @throws NonSuccessResponseException
     * @throws UnauthorizedException
     * @throws InvalidResponseDataException
     * @throws NonSuccessResponseException
     * @throws DuplicateFileException
     */
    public function create(string $apiKey, string $sourceId, string $filename, string $content): void
    {
        $response = $this->handleRequest($apiKey, 'POST', $sourceId, $filename, $content);

        if (!$response->isSuccessful()) {
            if ($response instanceof JsonResponse) {
                $duplicateFileException = $this->createDuplicateFileExceptionFromResponse($response);

                if ($duplicateFileException instanceof DuplicateFileException) {
                    throw $duplicateFileException;
                }
            }

            throw new NonSuccessResponseException($response->getHttpResponse());
        }
    }

    /**
     * @param non-empty-string $apiKey
     * @param non-empty-string $sourceId
     * @param non-empty-string $filename
     *
     * @throws ClientExceptionInterface
     * @throws NetworkExceptionInterface
     * @throws CurlExceptionInterface
     * @throws RequestExceptionInterface
     * @throws NonSuccessResponseException
     * @throws UnauthorizedException
     * @throws NotFoundException
     */
    public function read(string $apiKey, string $sourceId, string $filename): string
    {
        $response = $this->handleRequest($apiKey, 'GET', $sourceId, $filename);

        if (!$response->isSuccessful()) {
            if (404 === $response->getStatusCode()) {
                throw new NotFoundException($filename);
            }

            throw new NonSuccessResponseException($response->getHttpResponse());
        }

        return $response->getHttpResponse()->getBody()->getContents();
    }

    /**
     * @param non-empty-string $apiKey
     * @param non-empty-string $sourceId
     * @param non-empty-string $filename
     *
     * @throws ClientExceptionInterface
     * @throws NetworkExceptionInterface
     * @throws CurlExceptionInterface
     * @throws RequestExceptionInterface
     * @throws NonSuccessResponseException
     * @throws UnauthorizedException
     * @throws NonSuccessResponseException
     */
    public function update(string $apiKey, string $sourceId, string $filename, string $content): void
    {
        $response = $this->handleRequest($apiKey, 'PUT', $sourceId, $filename, $content);

        if (!$response->isSuccessful()) {
            throw new NonSuccessResponseException($response->getHttpResponse());
        }
    }

    /**
     * @param non-empty-string $apiKey
     * @param non-empty-string $sourceId
     * @param non-empty-string $filename
     *
     * @throws ClientExceptionInterface
     * @throws NetworkExceptionInterface
     * @throws CurlExceptionInterface
     * @throws RequestExceptionInterface
     * @throws NonSuccessResponseException
     * @throws UnauthorizedException
     */
    public function delete(string $apiKey, string $sourceId, string $filename): void
    {
        $response = $this->handleRequest($apiKey, 'DELETE', $sourceId, $filename);

        if (!$response->isSuccessful()) {
            throw new NonSuccessResponseException($response->getHttpResponse());
        }
    }

    /**
     * @param non-empty-string $apiKey
     * @param non-empty-string $method
     * @param non-empty-string $sourceId
     * @param non-empty-string $filename
     *
     * @throws ClientExceptionInterface
     * @throws CurlExceptionInterface
     * @throws NetworkExceptionInterface
     * @throws RequestExceptionInterface
     * @throws UnauthorizedException
     */
    private function handleRequest(
        string $apiKey,
        string $method,
        string $sourceId,
        string $filename,
        ?string $content = null
    ): ResponseInterface {
        $request = new Request(
            $method,
            $this->urlGenerator->generate(
                'file-source-file',
                [
                    'sourceId' => $sourceId,
                    'filename' => $filename
                ]
            )
        );

        if (is_string($content)) {
            $request = $request->withPayload(new Payload('application/yaml', $content));
        }

        $request = $request->withAuthentication(new BearerAuthentication($apiKey));

        return $this->serviceClient->sendRequest($request);
    }

    /**
     * @throws InvalidResponseDataException
     */
    private function createDuplicateFileExceptionFromResponse(JsonResponse $response): ?DuplicateFileException
    {
        $data = $response->getData();
        $type = $data['type'] ?? null;
        $type = is_string($type) ? $type : null;

        if (!is_string($type)) {
            return null;
        }

        $context = $data['context'];
        if (!is_array($context)) {
            return null;
        }

        if ('duplicate-file-path' === $type) {
            $path = $context['path'] ?? null;

            return new DuplicateFileException(is_string($path) ? $path : null);
        }

        return null;
    }
}
