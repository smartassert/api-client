<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Request;

use SmartAssert\ApiClient\Request\Body\BodyInterface;
use SmartAssert\ApiClient\Request\Header\HeaderInterface;

readonly class RequestSpecification
{
    public function __construct(
        public string $method,
        public RouteRequirements $routeRequirements,
        public ?HeaderInterface $header = null,
        public ?BodyInterface $body = null,
    ) {
    }

    /**
     * @return non-empty-string
     */
    public function getName(): string
    {
        return strtolower($this->method) . '_' . $this->routeRequirements->name;
    }
}
