<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Exception\Http;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\ApiClient\Exception\ClientExceptionInterface;
use SmartAssert\ApiClient\Request\RequestSpecification;

class HttpException extends \Exception implements ClientExceptionInterface
{
    public function __construct(
        private readonly RequestSpecification $requestSpecification,
        private readonly RequestInterface $request,
        private readonly ResponseInterface $response,
    ) {
        parent::__construct('', $response->getStatusCode());
    }

    public function getRequest(): RequestInterface
    {
        return $this->request;
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }

    public function getRequestSpecification(): RequestSpecification
    {
        return $this->requestSpecification;
    }

    public function getInnerException(): HttpException
    {
        return $this;
    }
}
