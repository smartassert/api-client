<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\FooException;

class IncompleteDataException extends \Exception
{
    /**
     * @param array<mixed>     $data
     * @param non-empty-string $missingKey
     */
    public function __construct(
        public array $data,
        public string $missingKey,
    ) {
        parent::__construct(
            sprintf(
                'Data missing key "%s", found "%s".',
                $this->missingKey,
                implode(', ', array_keys($this->data)),
            )
        );
    }
}
