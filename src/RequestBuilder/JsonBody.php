<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\RequestBuilder;

readonly class JsonBody extends Body
{
    /**
     * @param array<mixed> $data
     */
    public function __construct(array $data)
    {
        parent::__construct('application/json', (string) json_encode($data));
    }
}
