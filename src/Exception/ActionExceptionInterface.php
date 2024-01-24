<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Exception;

use Psr\Http\Message\RequestInterface;

interface ActionExceptionInterface extends \Throwable
{
    /**
     * @return non-empty-string
     */
    public function getName(): string;

    public function getRequest(): RequestInterface;
}
