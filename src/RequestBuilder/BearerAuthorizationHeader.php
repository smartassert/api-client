<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\RequestBuilder;

readonly class BearerAuthorizationHeader extends AuthorizationHeader
{
    public function __construct(string $value)
    {
        parent::__construct('Bearer ' . $value);
    }
}
