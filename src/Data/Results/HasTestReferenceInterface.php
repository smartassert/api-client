<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Data\Results;

interface HasTestReferenceInterface
{
    public function getTestReference(): ResourceReference;
}
