<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Factory\Source;

use SmartAssert\ApiClient\Data\Source\FileSource;
use SmartAssert\ApiClient\Data\Source\GitSource;
use SmartAssert\ApiClient\Data\Source\SourceInterface;
use SmartAssert\ApiClient\Exception\IncompleteDataException;

readonly class SourceFactory
{
    /**
     * @param array<mixed> $data
     *
     * @throws IncompleteDataException
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
     *
     * @throws IncompleteDataException
     */
    public function createFileSource(array $data): FileSource
    {
        $id = $data['id'] ?? null;
        $id = is_string($id) ? trim($id) : null;
        if ('' === $id || null === $id) {
            throw new IncompleteDataException($data, 'id');
        }

        $label = $data['label'] ?? null;
        $label = is_string($label) ? trim($label) : null;
        if ('' === $label || null === $label) {
            throw new IncompleteDataException($data, 'label');
        }

        $deletedAt = $data['deleted_at'] ?? null;
        $deletedAt = is_int($deletedAt) ? $deletedAt : null;
        $deletedAt = $deletedAt > 0 ? $deletedAt : null;

        return new FileSource($id, $label, $deletedAt);
    }

    /**
     * @param array<mixed> $data
     *
     * @throws IncompleteDataException
     */
    public function createGitSource(array $data): GitSource
    {
        $id = $data['id'] ?? null;
        $id = is_string($id) ? trim($id) : null;
        if ('' === $id || null === $id) {
            throw new IncompleteDataException($data, 'id');
        }

        $label = $data['label'] ?? null;
        $label = is_string($label) ? trim($label) : null;
        if ('' === $label || null === $label) {
            throw new IncompleteDataException($data, 'label');
        }

        $hostUrl = $data['host_url'] ?? null;
        $hostUrl = is_string($hostUrl) ? trim($hostUrl) : null;
        if ('' === $hostUrl || null === $hostUrl) {
            throw new IncompleteDataException($data, 'host_url');
        }

        $path = $data['path'] ?? null;
        $path = is_string($path) ? trim($path) : null;
        if ('' === $path || null === $path) {
            throw new IncompleteDataException($data, 'path');
        }

        $hasCredentials = $data['has_credentials'] ?? null;
        $hasCredentials = is_bool($hasCredentials) ? $hasCredentials : false;

        $deletedAt = $data['deleted_at'] ?? null;
        $deletedAt = is_int($deletedAt) ? $deletedAt : null;
        $deletedAt = $deletedAt > 0 ? $deletedAt : null;

        return new GitSource($id, $label, $hostUrl, $path, $hasCredentials, $deletedAt);
    }
}
