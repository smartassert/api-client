<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Data\Results;

readonly class StepPassedEvent extends AbstractEncapsulatingEvent implements EventInterface, HasStepReferenceInterface
{
    public function __construct(
        EventInterface $source,
        private StepInterface $step,
    ) {
        parent::__construct($source);
    }

    public function getStepReference(): ResourceReference
    {
        return $this->getResourceReference();
    }

    public function getStep(): StepInterface
    {
        return $this->step;
    }
}
