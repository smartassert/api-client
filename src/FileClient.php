<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient;

use GuzzleHttp\Psr7\Request as HttpRequest;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\ApiClient\FooException\File\DuplicateFileException;
use SmartAssert\ApiClient\FooException\File\NotFoundException as FileNotFoundException;
use SmartAssert\ApiClient\FooException\Http\HttpClientException;
use SmartAssert\ApiClient\FooException\Http\HttpException;
use SmartAssert\ApiClient\FooException\Http\NotFoundException;
use SmartAssert\ApiClient\FooException\Http\UnauthorizedException;
use SmartAssert\ApiClient\ServiceClient\HttpHandler;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

readonly class FileClient
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private HttpHandler $httpHandler,
    ) {
    }

    /**
     * @param non-empty-string $apiKey
     * @param non-empty-string $sourceId
     * @param non-empty-string $filename
     *
     * @throws DuplicateFileException
     * @throws HttpClientException
     * @throws HttpException
     * @throws NotFoundException
     * @throws UnauthorizedException
     */
    public function create(string $apiKey, string $sourceId, string $filename, string $content): void
    {
        try {
            $this->handleRequest($apiKey, 'POST', $sourceId, $filename, $content);
        } catch (HttpException $httpException) {
            $response = $httpException->response;

            if ('application/json' === $response->getHeaderLine('content-type')) {
                $responseData = json_decode($response->getBody()->getContents(), true);

                if (is_array($responseData) && 'duplicate' === ($responseData['class'] ?? null)) {
                    throw new DuplicateFileException($filename);
                }
            }

            throw $httpException;
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
     */
    public function read(string $apiKey, string $sourceId, string $filename): string
    {
        try {
            $response = $this->handleRequest($apiKey, 'GET', $sourceId, $filename);
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
     */
    public function update(string $apiKey, string $sourceId, string $filename, string $content): void
    {
        try {
            $this->handleRequest($apiKey, 'PUT', $sourceId, $filename, $content);
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
     */
    public function delete(string $apiKey, string $sourceId, string $filename): void
    {
        try {
            $this->handleRequest($apiKey, 'DELETE', $sourceId, $filename);
        } catch (NotFoundException | UnauthorizedException) {
            throw new FileNotFoundException($filename);
        }
    }

    /**
     * @param non-empty-string $apiKey
     * @param non-empty-string $method
     * @param non-empty-string $sourceId
     * @param non-empty-string $filename
     *
     * @throws HttpClientException
     * @throws HttpException
     * @throws NotFoundException
     * @throws UnauthorizedException
     */
    private function handleRequest(
        string $apiKey,
        string $method,
        string $sourceId,
        string $filename,
        ?string $content = null
    ): ResponseInterface {
        $headers = [
            'authorization' => 'Bearer ' . $apiKey,
            'translate-authorization-to' => 'api-token',
        ];

        if (is_string($content)) {
            $headers['content-type'] = 'application/yaml';
        }

        if ('GET' === $method) {
            $headers['accept'] = 'application/yaml, text/x-yaml';
        }

        $request = new HttpRequest(
            $method,
            $this->urlGenerator->generate(
                'file-source-file',
                [
                    'sourceId' => $sourceId,
                    'filename' => $filename
                ]
            ),
            $headers,
            $content
        );

        return $this->httpHandler->sendRequest($request);
    }
}
