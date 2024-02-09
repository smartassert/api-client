<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Exception\Error;

use SmartAssert\ServiceRequest\Error\ErrorInterface;

class ErrorException extends \Exception
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
