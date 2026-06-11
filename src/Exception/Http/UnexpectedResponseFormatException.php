<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Exception\Http;

use SmartAssert\ApiClient\Exception\ClientExceptionInterface;
use SmartAssert\ApiClient\Request\RequestSpecification;

class UnexpectedResponseFormatException extends \Exception implements ClientExceptionInterface
{
    public function __construct(
        private readonly RequestSpecification $requestSpecification,
        private readonly string $contentType,
        private readonly ?string $decodedDataType,
    ) {
        parent::__construct();
    }

    public function getContentType(): string
    {
        return $this->contentType;
    }

    public function getDecodedDataType(): ?string
    {
        return $this->decodedDataType;
    }

    public function getRequestSpecification(): RequestSpecification
    {
        return $this->requestSpecification;
    }

    public function getInnerException(): UnexpectedResponseFormatException
    {
        return $this;
    }
}
