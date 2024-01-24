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
        string $name,
        RequestInterface $request,
        private readonly ClientExceptionInterface $clientException
    ) {
        parent::__construct($name, $request);
    }

    public function getHttpClientException(): ClientExceptionInterface
    {
        return $this->clientException;
    }
}
