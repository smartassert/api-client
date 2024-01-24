<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Exception;

use Psr\Http\Message\ResponseInterface;

interface ResponseExceptionInterface extends ActionExceptionInterface
{
    public function getResponse(): ResponseInterface;
}
