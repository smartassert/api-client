<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Factory\JobCoordinator;

use SmartAssert\ApiClient\Data\JobCoordinator\Job\ComponentPreparationFailure;

readonly class ComponentPreparationFailureFactory
{
    /**
     * @param array<mixed> $data
     */
    public function create(array $data): ?ComponentPreparationFailure
    {
        $type = $data['type'] ?? null;
        $type = is_string($type) ? $type : null;

        $code = $data['code'] ?? null;
        $code = is_int($code) ? $code : null;

        $message = $data['message'] ?? null;
        $message = is_string($message) ? $message : null;

        if (null === $type || null === $code || null === $message) {
            return null;
        }

        return new ComponentPreparationFailure($type, $code, $message);
    }
}
