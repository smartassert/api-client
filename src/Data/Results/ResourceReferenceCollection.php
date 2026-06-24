<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Data\Results;

/**
 * @implements \IteratorAggregate<ResourceReference>
 */
readonly class ResourceReferenceCollection implements \IteratorAggregate
{
    /**
     * @param ResourceReference[] $resourceReferences
     */
    public function __construct(
        public array $resourceReferences = [],
    ) {}

    public function getForLabel(string $label): ?ResourceReference
    {
        return array_find(
            $this->resourceReferences,
            fn ($resourceReference) => $resourceReference->label === $label,
        );
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->resourceReferences);
    }
}
