<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Model;

readonly class ApiKey
{
    /**
     * @param ?non-empty-string $label
     * @param non-empty-string  $key
     */
    public function __construct(
        public ?string $label,
        public string $key,
    ) {
    }
}
