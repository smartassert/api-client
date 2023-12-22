<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Data\User;

readonly class Token
{
    /**
     * @param non-empty-string $token
     * @param non-empty-string $refreshToken
     */
    public function __construct(
        public string $token,
        public string $refreshToken,
    ) {
    }
}
