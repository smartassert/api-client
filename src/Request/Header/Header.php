<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Request\Header;

readonly class Header implements HeaderInterface
{
    public function __construct(
        public string $name,
        public string $value,
    ) {}

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [
            $this->name => $this->value,
        ];
    }
}
