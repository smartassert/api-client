<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Model\Source;

readonly class FileSource
{
    /**
     * @param non-empty-string $id
     * @param non-empty-string $label
     */
    public function __construct(
        public string $id,
        public string $label,
    ) {
    }
}
