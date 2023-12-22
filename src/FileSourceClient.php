<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient;

use SmartAssert\ApiClient\Data\Source\FileSource;
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

readonly class FileSourceClient
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private SourceFactory $sourceFactory,
        private HttpHandler $httpHandler,
        private RequestBuilder $requestBuilder,
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
        $request = $this->requestBuilder
            ->create('POST', $this->generateUrl())
            ->withApiKeyAuthorization($apiKey)
            ->withFormData(['label' => $label])
            ->get()
        ;

        return $this->sourceFactory->createFileSource($this->httpHandler->getJson($request));
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
        $request = $this->requestBuilder
            ->create('PUT', $this->generateUrl($id))
            ->withApiKeyAuthorization($apiKey)
            ->withFormData(['label' => $label])
            ->get()
        ;

        return $this->sourceFactory->createFileSource($this->httpHandler->getJson($request));
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
        $request = $this->requestBuilder
            ->create('GET', $this->urlGenerator->generate('file-source-list', ['sourceId' => $id]))
            ->withApiKeyAuthorization($apiKey)
            ->get()
        ;

        $data = $this->httpHandler->getJson($request);

        $filenames = [];
        foreach ($data as $filename) {
            if (is_string($filename) && '' !== $filename) {
                $filenames[] = $filename;
            }
        }

        return $filenames;
    }

    private function generateUrl(?string $id = null): string
    {
        return $this->urlGenerator->generate('file-source', ['sourceId' => $id]);
    }
}
