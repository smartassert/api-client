<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Data\Results;

interface EventInterface
{
    /**
     * @return positive-int
     */
    public function getSequenceNumber(): int;

    /**
     * @return non-empty-string
     */
    public function getType(): string;

    /**
     * @return array<mixed>
     */
    public function getBody(): array;

    public function getResourceReference(): ResourceReference;

    public function getRelatedReferences(): ?ResourceReferenceCollection;
}
