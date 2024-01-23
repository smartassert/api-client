<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Factory\User;

use SmartAssert\ApiClient\Data\User\ApiKey;
use SmartAssert\ApiClient\Exception\IncompleteDataException;
use SmartAssert\ApiClient\Factory\AbstractFactory;

readonly class ApiKeyFactory extends AbstractFactory
{
    /**
     * @param array<mixed> $data
     */
    public function create(array $data): ?ApiKey
    {
        try {
            $key = $this->getNonEmptyString($data, 'key');
        } catch (IncompleteDataException) {
            return null;
        }

        return new ApiKey($this->getNullableNonEmptyString($data, 'label'), $key);
    }
}
