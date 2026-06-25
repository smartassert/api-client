<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Data\Results;

readonly class LifecycleEvent extends AbstractEncapsulatingEvent implements EventInterface
{
    public function getJobMetadata(): JobMetadataInterface
    {
        return new JobMetadata($this->getResourceReference());
    }
}
