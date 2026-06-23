<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Data\Results;

abstract readonly class AbstractEncapsulatingEvent implements EventInterface
{
    public function __construct(
        private EventInterface $source,
    ) {}

    public function getSequenceNumber(): int
    {
        return $this->source->getSequenceNumber();
    }

    public function getType(): string
    {
        return $this->source->getType();
    }

    public function getBody(): array
    {
        return $this->source->getBody();
    }

    public function getResourceReference(): ResourceReference
    {
        return $this->source->getResourceReference();
    }

    public function getRelatedReferences(): ?ResourceReferenceCollection
    {
        return $this->source->getRelatedReferences();
    }
}
