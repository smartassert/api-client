<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Data\Results;

readonly class ResourceReferenceCollection
{
    /**
     * @param ResourceReference[] $resourceReferences
     */
    public function __construct(
        public array $resourceReferences = [],
    ) {}
}
