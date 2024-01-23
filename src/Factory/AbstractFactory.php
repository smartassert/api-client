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
        $value = $data[$key] ?? null;
        $value = is_string($value) ? trim($value) : null;
        if ('' === $value || null === $value) {
            throw new IncompleteDataException($data, $key);
        }

        return $value;
    }
}
