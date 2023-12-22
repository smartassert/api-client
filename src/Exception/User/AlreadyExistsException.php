<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Exception\User;

use Psr\Http\Message\ResponseInterface;

class AlreadyExistsException extends \Exception
{
    public function __construct(
        public readonly string $email,
        public readonly ResponseInterface $response,
    ) {
        parent::__construct(sprintf('User "%s" already exists', $this->email));
    }
}
