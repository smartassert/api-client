<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\NetworkExceptionInterface;
use Psr\Http\Client\RequestExceptionInterface;
use SmartAssert\ServiceClient\Authentication\BearerAuthentication;
use SmartAssert\ServiceClient\Client as ServiceClient;
use SmartAssert\ServiceClient\Exception\CurlExceptionInterface;
use SmartAssert\ServiceClient\Exception\NonSuccessResponseException;
use SmartAssert\ServiceClient\Exception\UnauthorizedException;
use SmartAssert\ServiceClient\Payload\Payload;
use SmartAssert\ServiceClient\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

readonly class FileClient
{
    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private ServiceClient $serviceClient,
    ) {
    }

    /**
     * @param non-empty-string $apiKey
     * @param non-empty-string $sourceId
     *
     * @throws ClientExceptionInterface
     * @throws NetworkExceptionInterface
     * @throws CurlExceptionInterface
     * @throws RequestExceptionInterface
     * @throws NonSuccessResponseException
     * @throws UnauthorizedException
     */
    public function create(string $apiKey, string $sourceId, string $filename, string $content): void
    {
        $request = (new Request(
            'POST',
            $this->urlGenerator->generate(
                'file-source-file',
                [
                    'sourceId' => $sourceId,
                    'filename' => $filename
                ]
            )
        ))->withPayload(new Payload('application/yaml', $content));

        $request = $request->withAuthentication(new BearerAuthentication($apiKey));

        $response = $this->serviceClient->sendRequest($request);

        if (!$response->isSuccessful()) {
            throw new NonSuccessResponseException($response->getHttpResponse());
        }
    }

    /**
     * @param non-empty-string $apiKey
     * @param non-empty-string $sourceId
     *
     * @throws ClientExceptionInterface
     * @throws NetworkExceptionInterface
     * @throws CurlExceptionInterface
     * @throws RequestExceptionInterface
     * @throws NonSuccessResponseException
     * @throws UnauthorizedException
     */
    public function read(string $apiKey, string $sourceId, string $filename): string
    {
        $request = new Request(
            'GET',
            $this->urlGenerator->generate(
                'file-source-file',
                [
                    'sourceId' => $sourceId,
                    'filename' => $filename
                ]
            )
        );

        $request = $request->withAuthentication(new BearerAuthentication($apiKey));

        $response = $this->serviceClient->sendRequest($request);

        if (!$response->isSuccessful()) {
            throw new NonSuccessResponseException($response->getHttpResponse());
        }

        return $response->getHttpResponse()->getBody()->getContents();
    }
}
