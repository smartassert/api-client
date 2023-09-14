<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Model;

readonly class User
{
    /**
     * @param non-empty-string $id
     * @param non-empty-string $userIdentifier
     */
    public function __construct(
        public string $id,
        public string $userIdentifier,
    ) {
    }
}
