<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Data\Results;

readonly class TestMetadata implements TestMetadataInterface
{
    public function __construct(
        private ResourceReference $resourceReference,
    ) {}

    public function getName(): string
    {
        return $this->resourceReference->label;
    }

    public function getResourceReference(): ResourceReference
    {
        return $this->resourceReference;
    }
}
