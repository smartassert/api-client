<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Exception\Http;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\ApiClient\Exception\ActionException;
use SmartAssert\ApiClient\Exception\ResponseExceptionInterface;

class HttpException extends ActionException implements ResponseExceptionInterface
{
    public function __construct(
        string $requestName,
        RequestInterface $request,
        private readonly ResponseInterface $response,
    ) {
        parent::__construct($requestName, $request, $response->getStatusCode());
    }

    public function getResponse(): ResponseInterface
    {
        return $this->response;
    }
}
