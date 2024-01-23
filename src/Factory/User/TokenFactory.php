<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Factory\User;

use SmartAssert\ApiClient\Data\User\Token;
use SmartAssert\ApiClient\Exception\IncompleteDataException;

readonly class TokenFactory
{
    /**
     * @param array<mixed> $data
     *
     * @throws IncompleteDataException
     */
    public function create(array $data): Token
    {
        $token = $data['token'] ?? null;
        $token = is_string($token) ? trim($token) : null;
        if ('' === $token || null === $token) {
            throw new IncompleteDataException($data, 'token');
        }

        $refreshToken = $data['refresh_token'] ?? null;
        $refreshToken = is_string($refreshToken) ? trim($refreshToken) : null;
        if ('' === $refreshToken || null === $refreshToken) {
            throw new IncompleteDataException($data, 'refresh_token');
        }

        return new Token($token, $refreshToken);
    }
}
