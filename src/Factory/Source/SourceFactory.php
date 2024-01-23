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
        return new FileSource(
            $this->getSourceId($data),
            $this->getSourceLabel($data),
            $this->getSourceDeletedAt($data)
        );
    }

    /**
     * @param array<mixed> $data
     *
     * @throws IncompleteDataException
     */
    public function createGitSource(array $data): GitSource
    {
        $id = $this->getSourceId($data);
        $label = $this->getSourceLabel($data);

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

        $deletedAt = $this->getSourceDeletedAt($data);

        return new GitSource($id, $label, $hostUrl, $path, $hasCredentials, $deletedAt);
    }

    /**
     * @param array<mixed> $data
     *
     * @return non-empty-string
     *
     * @throws IncompleteDataException
     */
    private function getSourceId(array $data): string
    {
        $id = $data['id'] ?? null;
        $id = is_string($id) ? trim($id) : null;
        if ('' === $id || null === $id) {
            throw new IncompleteDataException($data, 'id');
        }

        return $id;
    }

    /**
     * @param array<mixed> $data
     *
     * @return non-empty-string
     *
     * @throws IncompleteDataException
     */
    private function getSourceLabel(array $data): string
    {
        $label = $data['label'] ?? null;
        $label = is_string($label) ? trim($label) : null;
        if ('' === $label || null === $label) {
            throw new IncompleteDataException($data, 'label');
        }

        return $label;
    }

    /**
     * @param array<mixed> $data
     *
     * @return ?int<1, max>
     */
    private function getSourceDeletedAt(array $data): ?int
    {
        $deletedAt = $data['deleted_at'] ?? null;
        $deletedAt = is_int($deletedAt) ? $deletedAt : null;

        return $deletedAt > 0 ? $deletedAt : null;
    }
}
