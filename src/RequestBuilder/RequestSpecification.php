<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\RequestBuilder;

readonly class RequestSpecification
{
    public function __construct(
        public string $method,
        public RouteRequirements $routeRequirements,
        public ?HeaderInterface $header = null,
        public ?BodyInterface $body = null,
    ) {
    }
}
