<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\FooException\Http;

use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Message\RequestInterface;

class HttpClientException extends \Exception
{
    public function __construct(
        public readonly RequestInterface $request,
        public readonly ClientExceptionInterface $clientException,
    ) {
        parent::__construct(
            sprintf(
                '%s "%s": %s',
                $request->getMethod(),
                $request->getUri(),
                $clientException->getMessage(),
            ),
            $clientException->getCode(),
            $clientException
        );
    }
}
