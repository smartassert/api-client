<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Data\Source;

readonly class File
{
    /**
     * @param non-empty-string $path
     */
    public function __construct(
        public string $path,
        public int $size,
    ) {}
}
