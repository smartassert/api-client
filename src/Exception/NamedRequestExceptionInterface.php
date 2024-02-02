<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Exception;

interface NamedRequestExceptionInterface extends \Throwable
{
    /**
     * @return non-empty-string
     */
    public function getRequestName(): string;
}
