<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient;

use GuzzleHttp\Psr7\Request as HttpRequest;
use SmartAssert\ApiClient\Data\Source\SourceInterface;
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

readonly class SourceClient
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private SourceFactory $sourceFactory,
        private HttpHandler $httpHandler,
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
     */
    public function list(string $apiKey): array
    {
        $request = new HttpRequest(
            'GET',
            $this->urlGenerator->generate('sources'),
            [
                'authorization' => 'Bearer ' . $apiKey,
                'translate-authorization-to' => 'api-token',
            ]
        );

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
     */
    public function get(string $apiKey, string $id): ?SourceInterface
    {
        return $this->handleSourceRequest($apiKey, 'GET', $id);
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
     */
    public function delete(string $apiKey, string $id): ?SourceInterface
    {
        return $this->handleSourceRequest($apiKey, 'DELETE', $id);
    }

    /**
     * @param non-empty-string  $apiKey
     * @param non-empty-string  $method
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
    private function handleSourceRequest(
        string $apiKey,
        string $method,
        ?string $id
    ): ?SourceInterface {
        $request = new HttpRequest(
            $method,
            $this->urlGenerator->generate('source', ['sourceId' => $id]),
            [
                'authorization' => 'Bearer ' . $apiKey,
                'translate-authorization-to' => 'api-token',
            ]
        );

        return $this->sourceFactory->create($this->httpHandler->getJson($request));
    }
}
