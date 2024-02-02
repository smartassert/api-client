<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Exception\Http;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\ApiClient\Exception\HttpExceptionInterface;
use SmartAssert\ApiClient\Exception\NamedRequestExceptionInterface;

class HttpException extends \Exception implements HttpExceptionInterface, NamedRequestExceptionInterface
{
    /**
     * @param non-empty-string $requestName
     */
    public function __construct(
        private readonly string $requestName,
        private readonly RequestInterface $request,
        private readonly ResponseInterface $response,
    ) {
        parent::__construct('', $response->getStatusCode());
    }

    public function getRequestName(): string
    {
        return $this->requestName;
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }
}
