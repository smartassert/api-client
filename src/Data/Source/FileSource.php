<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Data\Source;

readonly class FileSource extends AbstractSource
{
    public function getType(): string
    {
        return 'file';
    }
}
