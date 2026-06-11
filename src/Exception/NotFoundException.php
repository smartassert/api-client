<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Exception;

use SmartAssert\ApiClient\Request\RequestSpecification;

class NotFoundException extends \Exception implements ClientExceptionInterface
{
    public function __construct(
        private readonly RequestSpecification $requestSpecification,
    ) {
        parent::__construct();
    }

    public function getRequestSpecification(): RequestSpecification
    {
        return $this->requestSpecification;
    }

    public function getInnerException(): NotFoundException
    {
        return $this;
    }
}
