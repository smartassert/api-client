<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Exception\Error;

use Psr\Http\Message\ResponseInterface;
use SmartAssert\ServiceRequest\Error\ErrorInterface;

class ErrorException extends \Exception
{
    public function __construct(
        public readonly ErrorInterface $error,
        public readonly ResponseInterface $response,
    ) {
        parent::__construct();
    }
}
