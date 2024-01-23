<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient;

use SmartAssert\ApiClient\Data\Source\SourceInterface;
use SmartAssert\ApiClient\Exception\Error\ErrorException;
use SmartAssert\ApiClient\Exception\Http\HttpClientException;
use SmartAssert\ApiClient\Exception\Http\HttpException;
use SmartAssert\ApiClient\Exception\Http\NotFoundException;
use SmartAssert\ApiClient\Exception\Http\UnauthorizedException;
use SmartAssert\ApiClient\Exception\Http\UnexpectedContentTypeException;
use SmartAssert\ApiClient\Exception\Http\UnexpectedDataException;
use SmartAssert\ApiClient\Exception\IncompleteDataException;
use SmartAssert\ApiClient\Factory\Source\SourceFactory;
use SmartAssert\ApiClient\RequestBuilder\RequestBuilder;
use SmartAssert\ApiClient\ServiceClient\HttpHandler;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

readonly class SourceClient
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
     *
     * @return SourceInterface[]
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
    public function list(string $apiKey): array
    {
        $request = $this->requestBuilder
            ->create('GET', $this->urlGenerator->generate('sources'))
            ->withApiKeyAuthorization($apiKey)
            ->get()
        ;

        $data = $this->httpHandler->getJson($request);

        $sources = [];
        foreach ($data as $sourceData) {
            if (is_array($sourceData)) {
                $source = $this->sourceFactory->create($sourceData);

                if ($source instanceof SourceInterface) {
                    $sources[] = $source;
                }
            }
        }

        return $sources;
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
    public function get(string $apiKey, string $id): ?SourceInterface
    {
        $request = $this->requestBuilder
            ->create('GET', $this->urlGenerator->generate('source', ['sourceId' => $id]))
            ->withApiKeyAuthorization($apiKey)
            ->get()
        ;

        return $this->sourceFactory->create($this->httpHandler->getJson($request));
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
    public function delete(string $apiKey, string $id): ?SourceInterface
    {
        $request = $this->requestBuilder
            ->create('DELETE', $this->urlGenerator->generate('source', ['sourceId' => $id]))
            ->withApiKeyAuthorization($apiKey)
            ->get()
        ;

        return $this->sourceFactory->create($this->httpHandler->getJson($request));
    }
}
