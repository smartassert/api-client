<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Factory\Source;

use SmartAssert\ApiClient\Model\Source\GitSource;
use SmartAssert\ArrayInspector\ArrayInspector;

readonly class GitSourceFactory
{
    /**
     * @param array<mixed> $data
     */
    public function create(array $data): ?GitSource
    {
        $modelDataInspector = new ArrayInspector($data);
        $id = $modelDataInspector->getNonEmptyString('id');
        $label = $modelDataInspector->getNonEmptyString('label');
        $hostUrl = $modelDataInspector->getNonEmptyString('host_url');
        $path = $modelDataInspector->getNonEmptyString('path');

        if (null === $id || null === $label || null === $hostUrl || null === $path) {
            return null;
        }

        $hasCredentials = $modelDataInspector->getBoolean('has_credentials');
        if (true !== $hasCredentials) {
            $hasCredentials = false;
        }

        $deletedAt = $modelDataInspector->getPositiveInteger('deleted_at');

        return new GitSource($id, $label, $hostUrl, $path, $hasCredentials, $deletedAt);
    }
}
