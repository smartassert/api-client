<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Data\JobCoordinator\Job;

readonly class Exception
{
    public function __construct(
        public string $class,
        public int $code,
        public string $message,
    ) {}
}
