<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient;

use SmartAssert\ApiClient\Data\Source\File;
use SmartAssert\ApiClient\Data\Source\FileSource;
use SmartAssert\ApiClient\Exception\ClientException;
use SmartAssert\ApiClient\Exception\IncompleteDataException;
use SmartAssert\ApiClient\Factory\Source\SourceFactory;
use SmartAssert\ApiClient\Request\Body\FormBody;
use SmartAssert\ApiClient\Request\Header\ApiKeyAuthorizationHeader;
use SmartAssert\ApiClient\Request\RequestSpecification;
use SmartAssert\ApiClient\Request\RouteRequirements;
use SmartAssert\ApiClient\ServiceClient\HttpHandler;

readonly class FileSourceClient
{
    public function __construct(
        private SourceFactory $sourceFactory,
        private HttpHandler $httpHandler,
    ) {}

    /**
     * @param non-empty-string $apiKey
     *
     * @throws ClientException
     */
    public function create(string $apiKey, string $label): FileSource
    {
        return $this->makeMutationRequest('POST', $apiKey, null, $label);
    }

    /**
     * @param non-empty-string $apiKey
     * @param non-empty-string $id
     *
     * @throws ClientException
     */
    public function update(string $apiKey, string $id, string $label): FileSource
    {
        return $this->makeMutationRequest('PUT', $apiKey, $id, $label);
    }

    /**
     * @param non-empty-string $apiKey
     * @param non-empty-string $id
     *
     * @return File[]
     *
     * @throws ClientException
     */
    public function list(string $apiKey, string $id): array
    {
        $data = $this->httpHandler->getJson(new RequestSpecification(
            'GET',
            new RouteRequirements('file-source-list', ['sourceId' => $id]),
            new ApiKeyAuthorizationHeader($apiKey),
        ));

        $files = [];

        foreach ($data as $fileData) {
            if (is_array($fileData)) {
                $path = $fileData['path'] ?? null;
                $path = is_string($path) ? trim($path) : null;

                $size = $fileData['size'] ?? null;
                $size = is_int($size) ? $size : null;

                if (is_string($path) && '' !== $path && is_int($size)) {
                    $files[] = new File($path, $size);
                }
            }
        }

        return $files;
    }

    /**
     * @throws ClientException
     */
    private function makeMutationRequest(string $method, string $apiKey, ?string $id, string $label): FileSource
    {
        $requestSpecification = new RequestSpecification(
            $method,
            new RouteRequirements('file-source', ['sourceId' => $id]),
            new ApiKeyAuthorizationHeader($apiKey),
            new FormBody(['label' => $label]),
        );

        try {
            return $this->sourceFactory->createFileSource(
                $this->httpHandler->getJson($requestSpecification)
            );
        } catch (IncompleteDataException $e) {
            throw new ClientException($requestSpecification->getName(), $e);
        }
    }
}
