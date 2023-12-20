<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient;

use GuzzleHttp\Psr7\Request as HttpRequest;
use SmartAssert\ApiClient\Data\Source\FileSource;
use SmartAssert\ApiClient\Factory\Source\SourceFactory;
use SmartAssert\ApiClient\FooException\Http\HttpClientException;
use SmartAssert\ApiClient\FooException\Http\HttpException;
use SmartAssert\ApiClient\FooException\Http\NotFoundException;
use SmartAssert\ApiClient\FooException\Http\UnauthorizedException;
use SmartAssert\ApiClient\FooException\Http\UnexpectedContentTypeException;
use SmartAssert\ApiClient\FooException\Http\UnexpectedDataException;
use SmartAssert\ApiClient\FooException\IncompleteDataException;
use SmartAssert\ApiClient\ServiceClient\HttpHandler;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

readonly class FileSourceClient
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private SourceFactory $sourceFactory,
        private HttpHandler $httpHandler,
    ) {
    }

    /**
     * @param non-empty-string $apiKey
     * @param non-empty-string $label
     *
     * @throws HttpClientException
     * @throws HttpException
     * @throws IncompleteDataException
     * @throws NotFoundException
     * @throws UnauthorizedException
     * @throws UnexpectedContentTypeException
     * @throws UnexpectedDataException
     */
    public function create(string $apiKey, string $label): FileSource
    {
        return $this->handleRequest($apiKey, 'POST', $label);
    }

    /**
     * @param non-empty-string $apiKey
     * @param non-empty-string $id
     * @param non-empty-string $label
     *
     * @throws HttpClientException
     * @throws HttpException
     * @throws IncompleteDataException
     * @throws NotFoundException
     * @throws UnauthorizedException
     * @throws UnexpectedContentTypeException
     * @throws UnexpectedDataException
     */
    public function update(string $apiKey, string $id, string $label): FileSource
    {
        return $this->handleRequest($apiKey, 'PUT', $label, $id);
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
     */
    public function list(string $apiKey, string $id): array
    {
        $request = new HttpRequest(
            'GET',
            $this->urlGenerator->generate('file-source-list', ['sourceId' => $id]),
            [
                'authorization' => 'Bearer ' . $apiKey,
                'translate-authorization-to' => 'api-token',
            ]
        );

        $data = $this->httpHandler->getJson($request);

        $filenames = [];
        foreach ($data as $filename) {
            if (is_string($filename) && '' !== $filename) {
                $filenames[] = $filename;
            }
        }

        return $filenames;
    }

    /**
     * @param non-empty-string  $apiKey
     * @param non-empty-string  $method
     * @param non-empty-string  $label
     * @param ?non-empty-string $id
     *
     * @throws HttpClientException
     * @throws HttpException
     * @throws IncompleteDataException
     * @throws NotFoundException
     * @throws UnauthorizedException
     * @throws UnexpectedContentTypeException
     * @throws UnexpectedDataException
     */
    private function handleRequest(string $apiKey, string $method, string $label, ?string $id = null): FileSource
    {
        $request = new HttpRequest(
            $method,
            $this->urlGenerator->generate('file-source', ['sourceId' => $id]),
            [
                'authorization' => 'Bearer ' . $apiKey,
                'translate-authorization-to' => 'api-token',
                'content-type' => 'application/x-www-form-urlencoded',
            ],
            http_build_query(['label' => $label])
        );

        return $this->sourceFactory->createFileSource($this->httpHandler->getJson($request));
    }
}
