<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Factory\JobCoordinator;

use SmartAssert\ApiClient\Data\JobCoordinator\Job\Exception;
use SmartAssert\ApiClient\Factory\AbstractFactory;

readonly class ExceptionFactory extends AbstractFactory
{
    /**
     * @param array<mixed> $data
     */
    public function create(array $data): Exception
    {
        $class = $data['class'] ?? null;
        $class = is_string($class) ? $class : '';

        $code = $data['code'] ?? null;
        $code = is_int($code) ? $code : 0;

        $message = $data['message'] ?? null;
        $message = is_string($message) ? $message : '';

        return new Exception($class, $code, $message);
    }
}
