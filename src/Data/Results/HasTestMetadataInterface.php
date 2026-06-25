<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Data\Results;

interface HasTestMetadataInterface
{
    public function getTestReference(): ResourceReference;
}
