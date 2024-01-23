<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\RequestBuilder;

readonly class AcceptableContentTypesHeader extends Header
{
    /**
     * @param non-empty-string[] $contentTypes
     */
    public function __construct(array $contentTypes)
    {
        parent::__construct('accept', implode(', ', $contentTypes));
    }
}
