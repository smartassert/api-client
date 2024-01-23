<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\RequestBuilder;

readonly class Body implements BodyInterface
{
    public function __construct(
        private string $contentType,
        private string $content,
    ) {
    }

    public function getContentType(): string
    {
        return $this->contentType;
    }

    public function getContent(): string
    {
        return $this->content;
    }
}
