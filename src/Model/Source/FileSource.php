<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Model\Source;

readonly class FileSource extends AbstractSource
{
    public function getType(): string
    {
        return 'file';
    }
}
