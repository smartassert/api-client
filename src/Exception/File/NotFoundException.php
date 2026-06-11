<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Exception\File;

use SmartAssert\ApiClient\Exception\ClientExceptionInterface;
use SmartAssert\ApiClient\Request\RequestSpecification;

class NotFoundException extends \Exception implements ClientExceptionInterface
{
    public function __construct(
        private readonly RequestSpecification $requestSpecification,
        public readonly ?string $filename,
    ) {
        parent::__construct('Not found: ' . $filename);
    }

    public function getRequestSpecification(): RequestSpecification
    {
        return $this->requestSpecification;
    }

    public function getInnerException(): NotFoundException
    {
        return $this;
    }
}
