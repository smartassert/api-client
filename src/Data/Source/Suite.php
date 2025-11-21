<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Data\Source;

readonly class Suite
{
    /**
     * @param non-empty-string $id
     * @param non-empty-string $label
     * @param string[]         $tests
     * @param ?positive-int    $deletedAt
     */
    public function __construct(
        public string $id,
        public string $sourceId,
        public string $label,
        public array $tests,
        public ?int $deletedAt,
    ) {}
}
