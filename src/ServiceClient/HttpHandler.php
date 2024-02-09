<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\ServiceClient;

use GuzzleHttp\Psr7\Request;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use SmartAssert\ApiClient\Exception\ClientException;
use SmartAssert\ApiClient\Exception\Error\ErrorException;
use SmartAssert\ApiClient\Exception\Error\Factory;
use SmartAssert\ApiClient\Exception\Http\HttpException;
use SmartAssert\ApiClient\Exception\Http\UnexpectedResponseFormatException;
use SmartAssert\ApiClient\Exception\NotFoundException;
use SmartAssert\ApiClient\Exception\UnauthorizedException;
use SmartAssert\ApiClient\Request\Body\BodyInterface;
use SmartAssert\ApiClient\Request\Header\HeaderInterface;
use SmartAssert\ApiClient\Request\RequestSpecification;
use SmartAssert\ServiceRequest\Error\ErrorInterface;
use SmartAssert\ServiceRequest\Exception\ErrorDeserializationException;
use SmartAssert\ServiceRequest\Exception\UnknownErrorClassException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

readonly class HttpHandler
{
    public function __construct(
        private ClientInterface $httpClient,
        private Factory $errorFactory,
        private StreamFactoryInterface $streamFactory,
        private UrlGeneratorInterface $urlGenerator,
    ) {
    }

    /**
     * @throws ClientException
     */
    public function sendRequest(RequestSpecification $requestSpecification): ResponseInterface
    {
        $request = $this->createRequest($requestSpecification);
        $requestName = $requestSpecification->getName();

        try {
            $response = $this->httpClient->sendRequest($request);
        } catch (ClientExceptionInterface $e) {
            throw new ClientException($requestName, $e);
        }

        $statusCode = $response->getStatusCode();
        if (401 === $statusCode) {
            throw new ClientException($requestName, new UnauthorizedException());
        }

        if (404 === $statusCode) {
            throw new ClientException($requestName, new NotFoundException());
        }

        if (200 !== $statusCode) {
            try {
                $error = $this->errorFactory->createFromResponse($response);

                $exception = $error instanceof ErrorInterface
                    ? new ErrorException($error)
                    : new HttpException($requestName, $request, $response);
            } catch (ErrorDeserializationException | UnknownErrorClassException) {
                $exception = new HttpException($requestName, $request, $response);
            }

            throw new ClientException($requestName, $exception);
        }

        return $response;
    }

    /**
     * @return array<mixed>
     *
     * @throws ClientException
     */
    public function getJson(RequestSpecification $requestSpecification): array
    {
        $requestName = $requestSpecification->getName();

        $response = $this->sendRequest($requestSpecification);

        $contentType = $response->getHeaderLine('content-type');
        if ('application/json' !== $contentType) {
            throw new ClientException(
                $requestName,
                new UnexpectedResponseFormatException($requestName, $contentType, null)
            );
        }

        $responseData = json_decode($response->getBody()->getContents(), true);
        if (!is_array($responseData)) {
            throw new ClientException(
                $requestName,
                new UnexpectedResponseFormatException($requestName, $contentType, gettype($responseData))
            );
        }

        return $responseData;
    }

    private function createRequest(RequestSpecification $requestSpecification): RequestInterface
    {
        $routeRequirements = $requestSpecification->routeRequirements;

        $request = new Request(
            $requestSpecification->method,
            $this->urlGenerator->generate($routeRequirements->name, $routeRequirements->parameters)
        );

        $header = $requestSpecification->header;
        if ($header instanceof HeaderInterface) {
            foreach ($header->toArray() as $name => $value) {
                $request = $request->withHeader($name, $value);
            }
        }

        $body = $requestSpecification->body;
        if ($body instanceof BodyInterface) {
            $request = $request->withHeader('content-type', $body->getContentType());
            $request = $request->withBody($this->streamFactory->createStream($body->getContent()));
        }

        return $request;
    }
}
