<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Data\Results;

readonly class LifecycleEvent extends AbstractEncapsulatingEvent implements EventInterface, HasJobReferenceInterface
{
    public function getJobReference(): ResourceReference
    {
        return $this->getResourceReference();
    }
}
