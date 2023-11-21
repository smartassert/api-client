<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Factory\Source;

use SmartAssert\ApiClient\Model\Source\FileSource;
use SmartAssert\ArrayInspector\ArrayInspector;

readonly class FileSourceFactory
{
    /**
     * @param array<mixed> $data
     */
    public function create(array $data): ?FileSource
    {
        $inspector = new ArrayInspector($data);

        $id = $inspector->getNonEmptyString('id');
        $label = $inspector->getNonEmptyString('label');
        $deletedAt = $inspector->getPositiveInteger('deleted_at');

        return null !== $id && null !== $label ? new FileSource($id, $label, $deletedAt) : null;
    }
}
