<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Data\Results;

interface HasStepReferenceInterface
{
    public function getStepReference(): ResourceReference;
}
