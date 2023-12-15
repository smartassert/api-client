<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\FooException\Http;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class UnexpectedDataException extends HttpException
{
    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        public readonly string $type,
    ) {
        parent::__construct($request, $response);
    }
}
