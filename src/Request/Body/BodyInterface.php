<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Request\Body;

interface BodyInterface
{
    public function getContentType(): string;

    public function getContent(): string;
}
