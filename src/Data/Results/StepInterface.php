<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Data\Results;

interface StepInterface
{
    /**
     * @return non-empty-string
     */
    public function getName(): string;

    public function getResourceReference(): ResourceReference;
}
