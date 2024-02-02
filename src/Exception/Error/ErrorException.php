<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Exception\Error;

use SmartAssert\ApiClient\Exception\ErrorExceptionInterface;
use SmartAssert\ApiClient\Exception\NamedRequestExceptionInterface;
use SmartAssert\ServiceRequest\Error\ErrorInterface;

class ErrorException extends \Exception implements ErrorExceptionInterface, NamedRequestExceptionInterface
{
    /**
     * @param non-empty-string $requestName
     */
    public function __construct(
        private readonly string $requestName,
        private readonly ErrorInterface $error,
    ) {
        parent::__construct();
    }

    public function getRequestName(): string
    {
        return $this->requestName;
    }

    public function getError(): ErrorInterface
    {
        return $this->error;
    }
}
