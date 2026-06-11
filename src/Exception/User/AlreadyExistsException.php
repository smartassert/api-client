<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Exception\User;

use Psr\Http\Message\ResponseInterface;
use SmartAssert\ApiClient\Exception\ClientExceptionInterface;
use SmartAssert\ApiClient\Request\RequestSpecification;

class AlreadyExistsException extends \Exception implements ClientExceptionInterface
{
    public function __construct(
        private readonly RequestSpecification $requestSpecification,
        public readonly string $email,
        public readonly ResponseInterface $response,
    ) {
        parent::__construct(sprintf('User "%s" already exists', $this->email));
    }

    public function getRequestSpecification(): RequestSpecification
    {
        return $this->requestSpecification;
    }

    public function getInnerException(): AlreadyExistsException
    {
        return $this;
    }
}
