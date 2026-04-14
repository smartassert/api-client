<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Data\JobCoordinator\Job;

readonly class ComponentPreparationFailure
{
    public function __construct(
        public string $type,
        public int $code,
        public string $message,
    ) {}
}
