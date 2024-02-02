<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Exception\Error;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\ApiClient\Exception\ErrorExceptionInterface;
use SmartAssert\ApiClient\Exception\Http\HttpException;
use SmartAssert\ServiceRequest\Error\ErrorInterface;

class ErrorException extends HttpException implements ErrorExceptionInterface
{
    public function __construct(
        string $requestName,
        RequestInterface $request,
        ResponseInterface $response,
        private readonly ErrorInterface $error,
    ) {
        parent::__construct($requestName, $request, $response);
    }

    public function getError(): ErrorInterface
    {
        return $this->error;
    }
}
