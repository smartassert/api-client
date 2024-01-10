<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Exception\Error;

use Psr\Http\Message\ResponseInterface;
use SmartAssert\ServiceRequest\Error\DuplicateObjectErrorInterface;

class DuplicateObjectException extends ErrorException
{
    public function __construct(DuplicateObjectErrorInterface $error, ResponseInterface $response)
    {
        parent::__construct($error, $response);
    }
}
