<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Exception\Error;

use Psr\Http\Message\ResponseInterface;
use SmartAssert\ServiceRequest\Error\ModifyReadOnlyEntityErrorInterface;

class ModifyReadOnlyEntityException extends ErrorException
{
    public function __construct(ModifyReadOnlyEntityErrorInterface $error, ResponseInterface $response)
    {
        parent::__construct($error, $response);
    }
}
