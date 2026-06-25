<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Data\Results;

interface HasJobReferenceInterface
{
    public function getJobReference(): ResourceReference;
}
