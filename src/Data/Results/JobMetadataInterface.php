<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Data\Results;

interface JobMetadataInterface
{
    /**
     * @return non-empty-string
     */
    public function getLabel(): string;

    public function getResourceReference(): ResourceReference;
}
