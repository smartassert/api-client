<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Exception\Error;

use Psr\Http\Message\ResponseInterface;
use SmartAssert\ServiceRequest\Error\BadRequestErrorInterface;

class BadRequestException extends ErrorException
{
    public function __construct(BadRequestErrorInterface $error, ResponseInterface $response)
    {
        parent::__construct($error, $response);
    }
}
