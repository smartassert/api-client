<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Exception;

use SmartAssert\ApiClient\Exception\Factory\IncompleteDataException;
use SmartAssert\ApiClient\Request\RequestSpecification;

class IncompleteResponseDataException extends \Exception implements ClientExceptionInterface
{
    public function __construct(
        private readonly RequestSpecification $requestSpecification,
        private readonly IncompleteDataException $innerException,
    ) {
        parent::__construct();
    }

    public function getRequestSpecification(): RequestSpecification
    {
        return $this->requestSpecification;
    }

    public function getInnerException(): IncompleteDataException
    {
        return $this->innerException;
    }
}
