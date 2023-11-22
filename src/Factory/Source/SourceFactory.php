<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Factory\Source;

use SmartAssert\ApiClient\Model\Source\FileSource;
use SmartAssert\ApiClient\Model\Source\GitSource;
use SmartAssert\ApiClient\Model\Source\SourceInterface;
use SmartAssert\ArrayInspector\ArrayInspector;

readonly class SourceFactory
{
    /**
     * @param array<mixed> $data
     */
    public function create(array $data): ?SourceInterface
    {
        $type = $data['type'] ?? null;

        if ('file' === $type) {
            return $this->createFileSource($data);
        }

        if ('git' === $type) {
            return $this->createGitSource($data);
        }

        return null;
    }

    /**
     * @param array<mixed> $data
     */
    private function createFileSource(array $data): ?FileSource
    {
        $inspector = new ArrayInspector($data);

        $id = $inspector->getNonEmptyString('id');
        $label = $inspector->getNonEmptyString('label');
        $deletedAt = $inspector->getPositiveInteger('deleted_at');

        return null !== $id && null !== $label ? new FileSource($id, $label, $deletedAt) : null;
    }

    /**
     * @param array<mixed> $data
     */
    private function createGitSource(array $data): ?GitSource
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