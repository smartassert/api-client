<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Exception\Http;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class HttpException extends \Exception
{
    public function __construct(
        public readonly RequestInterface $request,
        public readonly ResponseInterface $response,
    ) {
        parent::__construct(
            sprintf(
                '%s "%s": %s %s',
                $request->getMethod(),
                $request->getUri(),
                $response->getStatusCode(),
                $response->getReasonPhrase(),
            ),
            $response->getStatusCode()
        );
    }
}
