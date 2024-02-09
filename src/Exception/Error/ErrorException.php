<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Exception\Error;

use SmartAssert\ApiClient\Exception\ErrorExceptionInterface;
use SmartAssert\ServiceRequest\Error\ErrorInterface;

class ErrorException extends \Exception implements ErrorExceptionInterface
{
    public function __construct(
        private readonly ErrorInterface $error,
    ) {
        parent::__construct();
    }

    public function getError(): ErrorInterface
    {
        return $this->error;
    }
}
