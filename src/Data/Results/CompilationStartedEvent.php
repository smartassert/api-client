<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Data\Results;

readonly class CompilationStartedEvent extends AbstractEncapsulatingEvent implements EventInterface
{
    public function getTest(): TestInterface
    {
        return new Test($this->getResourceReference());
    }
}
