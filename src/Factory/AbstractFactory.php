<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Factory;

use SmartAssert\ApiClient\Exception\IncompleteDataException;

abstract readonly class AbstractFactory
{
    /**
     * @param array<mixed>     $data
     * @param non-empty-string $key
     *
     * @return non-empty-string
     *
     * @throws IncompleteDataException
     */
    protected function getNonEmptyString(array $data, string $key): string
    {
        $value = $this->getNullableNonEmptyString($data, $key);
        if (null === $value) {
            throw new IncompleteDataException($data, $key);
        }

        return $value;
    }

    /**
     * @param array<mixed>     $data
     * @param non-empty-string $key
     *
     * @return ?non-empty-string
     */
    protected function getNullableNonEmptyString(array $data, string $key): ?string
    {
        $value = $data[$key] ?? null;
        $value = is_string($value) ? trim($value) : null;

        return '' === $value || null === $value ? null : $value;
    }

    /**
     * @param array<mixed> $data
     *
     * @return ?int<1, max>
     */
    protected function getEntityDeletedAt(array $data): ?int
    {
        $deletedAt = $data['deleted_at'] ?? null;
        $deletedAt = is_int($deletedAt) ? $deletedAt : null;

        return $deletedAt > 0 ? $deletedAt : null;
    }
}
