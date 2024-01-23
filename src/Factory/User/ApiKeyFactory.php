<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Factory\User;

use SmartAssert\ApiClient\Data\User\ApiKey;

readonly class ApiKeyFactory
{
    /**
     * @param array<mixed> $data
     */
    public function create(array $data): ?ApiKey
    {
        $label = $data['label'] ?? null;
        $label = is_string($label) ? trim($label) : null;
        $label = '' === $label ? null : $label;

        $key = $data['key'] ?? null;
        $key = is_string($key) ? trim($key) : null;
        if ('' === $key || null === $key) {
            return null;
        }

        return new ApiKey($label, $key);
    }
}
