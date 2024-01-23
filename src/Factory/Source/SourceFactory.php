<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Factory\Source;

use SmartAssert\ApiClient\Data\Source\FileSource;
use SmartAssert\ApiClient\Data\Source\GitSource;
use SmartAssert\ApiClient\Data\Source\SourceInterface;
use SmartAssert\ApiClient\Exception\IncompleteDataException;
use SmartAssert\ApiClient\Factory\AbstractFactory;

readonly class SourceFactory extends AbstractFactory
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
            $this->getNonEmptyString($data, 'id'),
            $this->getNonEmptyString($data, 'label'),
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
        $id = $this->getNonEmptyString($data, 'id');
        $label = $this->getNonEmptyString($data, 'label');
        $hostUrl = $this->getNonEmptyString($data, 'host_url');
        $path = $this->getNonEmptyString($data, 'path');

        $hasCredentials = $data['has_credentials'] ?? null;
        $hasCredentials = is_bool($hasCredentials) ? $hasCredentials : false;

        $deletedAt = $this->getSourceDeletedAt($data);

        return new GitSource($id, $label, $hostUrl, $path, $hasCredentials, $deletedAt);
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
