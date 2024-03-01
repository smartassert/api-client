<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Data\Source;

readonly class SerializedSuite
{
    /**
     * @param non-empty-string      $id
     * @param non-empty-string      $suiteId
     * @param non-empty-string      $state
     * @param array<string, scalar> $parameters
     */
    public function __construct(
        public string $id,
        public string $suiteId,
        public string $state,
        public array $parameters,
    ) {
    }
}
