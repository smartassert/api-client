<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Request\Body;

readonly class FormBody extends Body
{
    /**
     * @param array<mixed> $data
     */
    public function __construct(array $data)
    {
        parent::__construct('application/x-www-form-urlencoded', http_build_query($data));
    }
}
