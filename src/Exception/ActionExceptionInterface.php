<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Exception;

use Psr\Http\Message\RequestInterface;

interface ActionExceptionInterface extends \Throwable, NamedRequestExceptionInterface
{
    public function getRequest(): RequestInterface;
}
