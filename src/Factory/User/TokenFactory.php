<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Factory\User;

use SmartAssert\ApiClient\Data\User\Token;
use SmartAssert\ApiClient\Exception\IncompleteDataException;
use SmartAssert\ApiClient\Factory\AbstractFactory;

readonly class TokenFactory extends AbstractFactory
{
    /**
     * @param array<mixed> $data
     *
     * @throws IncompleteDataException
     */
    public function create(array $data): Token
    {
        return new Token(
            $this->getNonEmptyString($data, 'token'),
            $this->getNonEmptyString($data, 'refresh_token')
        );
    }
}
