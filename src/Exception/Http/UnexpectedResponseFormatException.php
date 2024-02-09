<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Exception\Http;

class UnexpectedResponseFormatException extends \Exception
{
    public function __construct(
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
}
