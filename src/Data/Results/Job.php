<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Data\Results;

readonly class Job implements JobInterface
{
    public function __construct(
        private ResourceReference $resourceReference,
    ) {}

    public function getLabel(): string
    {
        return $this->resourceReference->label;
    }

    public function getResourceReference(): ResourceReference
    {
        return $this->resourceReference;
    }
}
