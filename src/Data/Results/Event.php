<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Data\Results;

readonly class Event implements EventInterface
{
    /**
     * @param positive-int     $sequenceNumber
     * @param non-empty-string $type
     * @param array<mixed>     $body
     */
    public function __construct(
        private int $sequenceNumber,
        private string $type,
        private ResourceReference $resourceReference,
        private array $body,
        private ?ResourceReferenceCollection $relatedReferences,
    ) {}

    public function getSequenceNumber(): int
    {
        return $this->sequenceNumber;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getBody(): array
    {
        return $this->body;
    }

    public function getResourceReference(): ResourceReference
    {
        return $this->resourceReference;
    }

    public function getRelatedReferences(): ?ResourceReferenceCollection
    {
        return $this->relatedReferences;
    }
}
