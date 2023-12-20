<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Factory\Source;

use SmartAssert\ApiClient\Data\Source\FileSource as FooFileSource;
use SmartAssert\ApiClient\Data\Source\GitSource as FooGitSource;
use SmartAssert\ApiClient\Data\Source\SourceInterface as FooSourceInterface;
use SmartAssert\ApiClient\FooException\IncompleteDataException;

readonly class SourceFactory
{
    /**
     * @param array<mixed> $data
     *
     * @throws IncompleteDataException
     */
    public function create(array $data): ?FooSourceInterface
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
    public function createFileSource(array $data): FooFileSource
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

        return new FooFileSource($id, $label, $deletedAt);
    }

    /**
     * @param array<mixed> $data
     *
     * @throws IncompleteDataException
     */
    public function createGitSource(array $data): FooGitSource
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

        return new FooGitSource($id, $label, $hostUrl, $path, $hasCredentials, $deletedAt);
    }
}
