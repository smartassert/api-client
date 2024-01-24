<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Exception;

use Psr\Http\Client\ClientExceptionInterface;

interface FailedRequestInterface extends ActionExceptionInterface
{
    public function getHttpClientException(): ClientExceptionInterface;
}
