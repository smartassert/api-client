<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Exception;

use Psr\Http\Client\ClientExceptionInterface as PsrClientExceptionInterface;
use SmartAssert\ApiClient\Request\RequestSpecification;

class HttpClientException extends \Exception implements ClientExceptionInterface
{
    public function __construct(
        private readonly RequestSpecification $requestSpecification,
        private readonly PsrClientExceptionInterface $clientException,
    ) {
        parent::__construct();
    }

    public function getRequestSpecification(): RequestSpecification
    {
        return $this->requestSpecification;
    }

    public function getInnerException(): PsrClientExceptionInterface
    {
        return $this->clientException;
    }
}
