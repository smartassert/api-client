<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Data\Results;

use SmartAssert\ApiClient\Data\Results\AbstractEncapsulatingEvent as BaseEvent;

readonly class CompilationStartedEvent extends BaseEvent implements EventInterface, HasTestMetadataInterface
{
    public function getTestReference(): ResourceReference
    {
        return $this->getResourceReference();
    }
}
