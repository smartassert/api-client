<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Client\RequestExceptionInterface;
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
    ) {
    }

    /**
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
        $response = $this->serviceClient->sendRequest(
            (new Request('POST', $this->urlGenerator->generate('file-source_create')))
                ->withAuthentication(
                    new BearerAuthentication($apiKey)
                )
                ->withPayload(
                    new UrlEncodedPayload([
                        'label' => $label,
                    ])
                )
        );

        return $this->handleFileSourceResponse($response);
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
        $response = $this->serviceClient->sendRequest(
            (new Request('GET', $this->urlGenerator->generate('file-source', ['sourceId' => $id])))
                ->withAuthentication(
                    new BearerAuthentication($apiKey)
                )
        );

        return $this->handleFileSourceResponse($response);
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
        $response = $this->serviceClient->sendRequest(
            (new Request('PUT', $this->urlGenerator->generate('file-source', ['sourceId' => $id])))
                ->withAuthentication(
                    new BearerAuthentication($apiKey)
                )
                ->withPayload(
                    new UrlEncodedPayload([
                        'label' => $label,
                    ])
                )
        );

        return $this->handleFileSourceResponse($response);
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
        $response = $this->serviceClient->sendRequest(
            (new Request('DELETE', $this->urlGenerator->generate('file-source', ['sourceId' => $id])))
                ->withAuthentication(
                    new BearerAuthentication($apiKey)
                )
        );

        return $this->handleFileSourceResponse($response);
    }

    /**
     * @throws InvalidModelDataException
     * @throws InvalidResponseDataException
     * @throws InvalidResponseTypeException
     * @throws NonSuccessResponseException
     */
    private function handleFileSourceResponse(ResponseInterface $response): FileSource
    {
        if (!$response->isSuccessful()) {
            throw new NonSuccessResponseException($response->getHttpResponse());
        }

        if (!$response instanceof JsonResponse) {
            throw InvalidResponseTypeException::create($response, JsonResponse::class);
        }

        $responseDataInspector = new ArrayInspector($response->getData());
        $modelData = $responseDataInspector->getArray('file_source');

        $modelDataInspector = new ArrayInspector($modelData);
        $id = $modelDataInspector->getNonEmptyString('id');
        $label = $modelDataInspector->getNonEmptyString('label');
        $deletedAt = $modelDataInspector->getPositiveInteger('deleted_at');

        if (null === $id || null === $label) {
            throw InvalidModelDataException::fromJsonResponse(FileSource::class, $response);
        }

        return new FileSource($id, $label, $deletedAt);
    }
}
