<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\ServiceClient;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\ApiClient\Exception\Error\ErrorException;
use SmartAssert\ApiClient\Exception\Error\Factory;
use SmartAssert\ApiClient\Exception\Http\HttpClientException;
use SmartAssert\ApiClient\Exception\Http\HttpException;
use SmartAssert\ApiClient\Exception\Http\NotFoundException;
use SmartAssert\ApiClient\Exception\Http\UnauthorizedException;
use SmartAssert\ApiClient\Exception\Http\UnexpectedContentTypeException;
use SmartAssert\ApiClient\Exception\Http\UnexpectedDataException;
use SmartAssert\ServiceRequest\Exception\ErrorDeserializationException;
use SmartAssert\ServiceRequest\Exception\UnknownErrorClassException;

readonly class HttpHandler
{
    public function __construct(
        private ClientInterface $httpClient,
        private Factory $exceptionFactory,
    ) {
    }

    /**
     * @throws ErrorException
     * @throws HttpClientException
     * @throws HttpException
     * @throws NotFoundException
     * @throws UnauthorizedException
     */
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        try {
            $response = $this->httpClient->sendRequest($request);
        } catch (ClientExceptionInterface $e) {
            throw new HttpClientException($request, $e);
        }

        $statusCode = $response->getStatusCode();
        if (401 === $statusCode) {
            throw new UnauthorizedException($request, $response);
        }

        if (404 === $statusCode) {
            throw new NotFoundException($request, $response);
        }

        if (200 !== $statusCode) {
            try {
                $exception = $this->exceptionFactory->createFromResponse($response);
                if (null === $exception) {
                    $exception = new HttpException($request, $response);
                }
            } catch (ErrorDeserializationException | UnknownErrorClassException) {
                $exception = new HttpException($request, $response);
            }

            throw $exception;
        }

        return $response;
    }

    /**
     * @return array<mixed>
     *
     * @throws ErrorException
     * @throws HttpClientException
     * @throws HttpException
     * @throws NotFoundException
     * @throws UnauthorizedException
     * @throws UnexpectedContentTypeException
     * @throws UnexpectedDataException
     */
    public function getJson(RequestInterface $request): array
    {
        $response = $this->sendRequest($request);

        $contentType = $response->getHeaderLine('content-type');
        if ('application/json' !== $contentType) {
            throw new UnexpectedContentTypeException($request, $response, $contentType);
        }

        $responseData = json_decode($response->getBody()->getContents(), true);
        if (!is_array($responseData)) {
            throw new UnexpectedDataException($request, $response, gettype($responseData));
        }

        return $responseData;
    }
}
