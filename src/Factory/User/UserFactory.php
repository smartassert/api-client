<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Factory\User;

use SmartAssert\ApiClient\Data\User\User;
use SmartAssert\ApiClient\Exception\IncompleteDataException;

readonly class UserFactory
{
    /**
     * @param array<mixed> $data
     *
     * @throws IncompleteDataException
     */
    public function create(array $data): User
    {
        $id = $data['id'] ?? null;
        $id = is_string($id) ? trim($id) : null;
        if ('' === $id || null === $id) {
            throw new IncompleteDataException($data, 'id');
        }

        $identifier = $data['user-identifier'] ?? null;
        $identifier = is_string($identifier) ? trim($identifier) : null;
        if ('' === $identifier || null === $identifier) {
            throw new IncompleteDataException($data, 'user-identifier');
        }

        return new User($id, $identifier);
    }
}
