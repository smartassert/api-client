<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Request\Body;

readonly class YamlBody extends Body
{
    public function __construct(string $content)
    {
        parent::__construct('application/yaml', $content);
    }
}
