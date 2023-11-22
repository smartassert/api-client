<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Client\RequestExceptionInterface;
use SmartAssert\ApiClient\Factory\Source\SourceFactory;
use SmartAssert\ApiClient\Model\Source\FileSource;
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

readonly class FileSourceClient
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private ServiceClient $serviceClient,
        private SourceFactory $sourceFactory,
    ) {
    }

    /**
     * @param non-empty-string $apiKey
     * @param non-empty-string $label
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
    public function create(string $apiKey, string $label): FileSource
    {
        return $this->handleFileSourceRequest($apiKey, 'POST', null, $label);
    }

    /**
     * @param non-empty-string $apiKey
     * @param non-empty-string $id
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
    public function get(string $apiKey, string $id): FileSource
    {
        return $this->handleFileSourceRequest($apiKey, 'GET', $id);
    }

    /**
     * @param non-empty-string $apiKey
     * @param non-empty-string $id
     * @param non-empty-string $label
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
    public function update(string $apiKey, string $id, string $label): FileSource
    {
        return $this->handleFileSourceRequest($apiKey, 'PUT', $id, $label);
    }

    /**
     * @param non-empty-string $apiKey
     * @param non-empty-string $id
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
    public function delete(string $apiKey, string $id): FileSource
    {
        return $this->handleFileSourceRequest($apiKey, 'DELETE', $id);
    }

    /**
     * @param non-empty-string $apiKey
     * @param non-empty-string $id
     *
     * @return non-empty-string[]
     *
     * @throws ClientExceptionInterface
     * @throws CurlExceptionInterface
     * @throws InvalidResponseDataException
     * @throws InvalidResponseTypeException
     * @throws NetworkExceptionInterface
     * @throws NonSuccessResponseException
     * @throws RequestExceptionInterface
     * @throws UnauthorizedException
     */
    public function list(string $apiKey, string $id): array
    {
        $request = new Request('GET', $this->urlGenerator->generate('file-source-list', ['sourceId' => $id]));
        $request = $request->withAuthentication(new BearerAuthentication($apiKey));

        $response = $this->serviceClient->sendRequest($request);

        if (!$response instanceof JsonResponse) {
            throw InvalidResponseTypeException::create($response, JsonResponse::class);
        }

        $responseDataInspector = new ArrayInspector($response->getData());
        $filenamesData = $responseDataInspector->getArray('files');

        $filenames = [];
        foreach ($filenamesData as $filename) {
            if (is_string($filename) && '' !== $filename) {
                $filenames[] = $filename;
            }
        }

        return $filenames;
    }

    /**
     * @param non-empty-string  $apiKey
     * @param non-empty-string  $method
     * @param ?non-empty-string $id
     * @param ?non-empty-string $label
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
    private function handleFileSourceRequest(
        string $apiKey,
        string $method,
        ?string $id,
        ?string $label = null
    ): FileSource {
        $request = new Request($method, $this->urlGenerator->generate('file-source', ['sourceId' => $id]));
        $request = $request->withAuthentication(new BearerAuthentication($apiKey));

        if (null !== $label) {
            $request = $request->withPayload(new UrlEncodedPayload(['label' => $label]));
        }

        $response = $this->serviceClient->sendRequest($request);

        return $this->handleFileSourceResponse($response);
    }

    /**
     * @throws InvalidModelDataException
     * @throws InvalidResponseDataException
     * @throws InvalidResponseTypeException
     */
    private function handleFileSourceResponse(ResponseInterface $response): FileSource
    {
        if (!$response instanceof JsonResponse) {
            throw InvalidResponseTypeException::create($response, JsonResponse::class);
        }

        $responseDataInspector = new ArrayInspector($response->getData());
        $modelData = $responseDataInspector->getArray('file_source');

        $source = $this->sourceFactory->create($modelData);
        if (!$source instanceof FileSource) {
            throw InvalidModelDataException::fromJsonResponse(FileSource::class, $response);
        }

        return $source;
    }
}
