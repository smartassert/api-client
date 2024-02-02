<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Exception\File;

use SmartAssert\ApiClient\Exception\NamedRequestExceptionInterface;

class NotFoundException extends \Exception implements NamedRequestExceptionInterface
{
    /**
     * @param non-empty-string $requestName
     */
    public function __construct(
        private readonly string $requestName,
        public readonly ?string $filename,
    ) {
        parent::__construct('Not found: ' . $filename);
    }

    public function getRequestName(): string
    {
        return $this->requestName;
    }
}
