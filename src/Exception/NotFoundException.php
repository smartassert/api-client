<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Exception;

class NotFoundException extends \Exception implements NamedRequestExceptionInterface
{
    /**
     * @param non-empty-string $requestName
     */
    public function __construct(
        private readonly string $requestName,
    ) {
        parent::__construct();
    }

    public function getRequestName(): string
    {
        return $this->requestName;
    }
}