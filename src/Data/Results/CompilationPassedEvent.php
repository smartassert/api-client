<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Data\Results;

use SmartAssert\ApiClient\Data\Results\AbstractEncapsulatingEvent as BaseEvent;

readonly class CompilationPassedEvent extends BaseEvent implements EventInterface, HasTestReferenceInterface
{
    public function getTestReference(): ResourceReference
    {
        return $this->getResourceReference();
    }

    public function getStepReferences(): ResourceReferenceCollection
    {
        return $this->getRelatedReferences() ?? new ResourceReferenceCollection();
    }
}
