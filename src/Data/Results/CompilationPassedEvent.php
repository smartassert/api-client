<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Data\Results;

readonly class CompilationPassedEvent extends AbstractEncapsulatingEvent implements EventInterface
{
    public function getTest(): TestInterface
    {
        return new Test($this->getResourceReference());
    }

    /**
     * @return StepInterface[]
     */
    public function getSteps(): array
    {
        $steps = [];

        $relatedReferences = $this->getRelatedReferences() ?? [];

        foreach ($relatedReferences as $relatedReference) {
            $steps[] = new Step($relatedReference);
        }

        return $steps;
    }
}
