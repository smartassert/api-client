<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Data\Results;

readonly class CompilationStartedEvent extends AbstractEncapsulatingEvent implements EventInterface
{
    public function getTestMetadata(): TestMetadataInterface
    {
        return new TestMetadata($this->getResourceReference());
    }
}
