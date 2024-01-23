<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Request;

class HeaderCollection implements HeaderInterface
{
    /**
     * @var HeaderInterface[]
     */
    private array $headers;

    /**
     * @param array<mixed> $headers
     */
    public function __construct(array $headers)
    {
        foreach ($headers as $header) {
            if ($header instanceof HeaderInterface) {
                $this->headers[] = $header;
            }
        }
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        $data = [];
        foreach ($this->headers as $header) {
            $data = array_merge($data, $header->toArray());
        }

        return $data;
    }
}
