<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Exception;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

interface HttpExceptionInterface extends \Throwable
{
    public function getRequest(): RequestInterface;

    public function getResponse(): ResponseInterface;
}
