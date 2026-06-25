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

    /**
     * @return StepMetadataInterface[]
     */
    public function getStepMetadataCollection(): array
    {
        $steps = [];

        $relatedReferences = $this->getRelatedReferences() ?? [];

        foreach ($relatedReferences as $relatedReference) {
            $steps[] = new StepMetadata($relatedReference);
        }

        return $steps;
    }
}
