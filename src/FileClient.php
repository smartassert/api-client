<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient;

use SmartAssert\ApiClient\Exception\File\DuplicateFileException;
use SmartAssert\ApiClient\Exception\File\NotFoundException as FileNotFoundException;
use SmartAssert\ApiClient\Exception\Http\HttpClientException;
use SmartAssert\ApiClient\Exception\Http\HttpException;
use SmartAssert\ApiClient\Exception\Http\NotFoundException;
use SmartAssert\ApiClient\Exception\Http\UnauthorizedException;
use SmartAssert\ApiClient\ServiceClient\HttpHandler;
use SmartAssert\ApiClient\ServiceClient\RequestBuilder;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

readonly class FileClient
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private HttpHandler $httpHandler,
        private RequestBuilder $requestBuilder,
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
        $request = $this->requestBuilder
            ->create('POST', $this->generateUrl($sourceId, $filename))
            ->withApiKeyAuthorization($apiKey)
            ->withBody('application/yaml', $content)
            ->get()
        ;

        try {
            $this->httpHandler->sendRequest($request);
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
        $request = $this->requestBuilder
            ->create('GET', $this->generateUrl($sourceId, $filename))
            ->withApiKeyAuthorization($apiKey)
            ->withAcceptableContentTypes(['application/yaml', 'text/x-yaml'])
            ->get()
        ;

        try {
            $response = $this->httpHandler->sendRequest($request);
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
        $request = $this->requestBuilder
            ->create('PUT', $this->generateUrl($sourceId, $filename))
            ->withApiKeyAuthorization($apiKey)
            ->withBody('application/yaml', $content)
            ->get()
        ;

        try {
            $this->httpHandler->sendRequest($request);
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
        $request = $this->requestBuilder
            ->create('DELETE', $this->generateUrl($sourceId, $filename))
            ->withApiKeyAuthorization($apiKey)
            ->get()
        ;

        try {
            $this->httpHandler->sendRequest($request);
        } catch (NotFoundException | UnauthorizedException) {
            throw new FileNotFoundException($filename);
        }
    }

    /**
     * @param non-empty-string $sourceId
     * @param non-empty-string $filename
     */
    private function generateUrl(string $sourceId, string $filename): string
    {
        return $this->urlGenerator->generate(
            'file-source-file',
            [
                'sourceId' => $sourceId,
                'filename' => $filename
            ]
        );
    }
}
