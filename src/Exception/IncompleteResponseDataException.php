<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Exception;

class IncompleteResponseDataException extends \Exception implements NamedRequestExceptionInterface
{
    /**
     * @param non-empty-string $requestName
     */
    public function __construct(
        public string $requestName,
        public IncompleteDataException $incompleteDataException,
    ) {
        parent::__construct(
            $requestName . ' response lacking data: ' . $incompleteDataException->getMessage(),
            0,
            $incompleteDataException
        );
    }

    public function getRequestName(): string
    {
        return $this->requestName;
    }
}
