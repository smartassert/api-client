<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Request\Header;

readonly class AuthorizationHeader extends Header
{
    public function __construct(string $value)
    {
        parent::__construct('authorization', $value);
    }
}
