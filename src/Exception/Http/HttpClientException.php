<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Exception\Http;

use Psr\Http\Client\ClientExceptionInterface;
use SmartAssert\ApiClient\Exception\HttpClientExceptionInterface;
use SmartAssert\ApiClient\Exception\NamedRequestExceptionInterface;

class HttpClientException extends \Exception implements HttpClientExceptionInterface, NamedRequestExceptionInterface
{
    /**
     * @param non-empty-string $requestName
     */
    public function __construct(
        private readonly string $requestName,
        private readonly ClientExceptionInterface $clientException
    ) {
        parent::__construct();
    }

    public function getRequestName(): string
    {
        return $this->requestName;
    }

    public function getHttpClientException(): ClientExceptionInterface
    {
        return $this->clientException;
    }
}
