<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Data\Results;

readonly class CompilationPassedEvent extends AbstractEncapsulatingEvent implements EventInterface
{
    public function getTestMetadata(): TestMetadataInterface
    {
        return new TestMetadata($this->getResourceReference());
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
