<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Exception;

use SmartAssert\ServiceRequest\Error\ErrorInterface;

interface ErrorExceptionInterface
{
    public function getError(): ErrorInterface;
}
