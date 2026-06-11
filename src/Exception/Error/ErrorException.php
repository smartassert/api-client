<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Exception\Error;

use SmartAssert\ApiClient\Exception\ClientExceptionInterface;
use SmartAssert\ApiClient\Request\RequestSpecification;
use SmartAssert\ServiceRequest\Error\ErrorInterface;

class ErrorException extends \Exception implements ClientExceptionInterface
{
    public function __construct(
        private readonly RequestSpecification $requestSpecification,
        private readonly ErrorInterface $error,
    ) {
        parent::__construct();
    }

    public function getError(): ErrorInterface
    {
        return $this->error;
    }

    public function getRequestSpecification(): RequestSpecification
    {
        return $this->requestSpecification;
    }

    public function getInnerException(): ErrorException
    {
        return $this;
    }
}
