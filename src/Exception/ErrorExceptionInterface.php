<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Exception;

use Psr\Http\Message\ResponseInterface;
use SmartAssert\ServiceRequest\Error\ErrorInterface;

interface ErrorExceptionInterface extends ActionExceptionInterface
{
    public function getResponse(): ResponseInterface;

    public function getError(): ErrorInterface;
}
