<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Exception\Http;

use SmartAssert\ApiClient\Exception\NamedRequestExceptionInterface;

class UnexpectedContentTypeException extends \Exception implements NamedRequestExceptionInterface
{
    /**
     * @param non-empty-string $requestName
     */
    public function __construct(
        private readonly string $requestName,
        public readonly string $contentType,
    ) {
        parent::__construct();
    }

    public function getRequestName(): string
    {
        return $this->requestName;
    }
}
