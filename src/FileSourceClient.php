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

        if (null === $id || null === $label) {
            throw InvalidModelDataException::fromJsonResponse(FileSource::class, $response);
        }

        return new FileSource($id, $label);
    }
}
