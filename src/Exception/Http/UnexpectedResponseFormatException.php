<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Exception\Http;

use SmartAssert\ApiClient\Exception\NamedRequestExceptionInterface;

class UnexpectedResponseFormatException extends \Exception implements NamedRequestExceptionInterface
{
    /**
     * @param non-empty-string $requestName
     */
    public function __construct(
        private readonly string $requestName,
        private readonly string $contentType,
        private readonly ?string $decodedDataType,
    ) {
        parent::__construct();
    }

    public function getRequestName(): string
    {
        return $this->requestName;
    }

    public function getContentType(): string
    {
        return $this->contentType;
    }

    public function getDecodedDataType(): ?string
    {
        return $this->decodedDataType;
    }
}
