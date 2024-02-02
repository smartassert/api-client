<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Exception\Http;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;
use SmartAssert\ApiClient\Exception\ActionException;
use SmartAssert\ApiClient\Exception\FailedRequestInterface;

class FailedRequestException extends ActionException implements FailedRequestInterface
{
    public function __construct(
        string $requestName,
        RequestInterface $request,
        private readonly ClientExceptionInterface $clientException
    ) {
        parent::__construct($requestName, $request);
    }

    public function getHttpClientException(): ClientExceptionInterface
    {
        return $this->clientException;
    }
}
