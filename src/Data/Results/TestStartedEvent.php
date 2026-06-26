<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Data\Results;

readonly class TestStartedEvent extends AbstractEncapsulatingEvent implements EventInterface, HasTestReferenceInterface
{
    public function __construct(
        EventInterface $source,
        private TestInterface $test
    ) {
        parent::__construct($source);
    }

    public function getTestReference(): ResourceReference
    {
        return $this->getResourceReference();
    }

    public function getStepReferences(): ResourceReferenceCollection
    {
        return $this->getRelatedReferences() ?? new ResourceReferenceCollection();
    }

    public function getTest(): TestInterface
    {
        return $this->test;
    }
}
