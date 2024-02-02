<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Exception\Http;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class UnexpectedDataException extends HttpException
{
    public function __construct(
        string $requestName,
        RequestInterface $request,
        ResponseInterface $response,
        public readonly string $type,
    ) {
        parent::__construct($requestName, $request, $response);
    }
}
