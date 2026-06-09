<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Data\Results;

readonly class ResourceReference
{
    /**
     * @param non-empty-string $label
     * @param non-empty-string $reference
     */
    public function __construct(
        public string $label,
        public string $reference,
    ) {}
}
