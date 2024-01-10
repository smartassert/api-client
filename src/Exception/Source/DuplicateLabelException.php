<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Exception\Source;

class DuplicateLabelException extends \Exception
{
    public function __construct(
        public readonly string $label,
    ) {
        parent::__construct('Duplicate label: ' . $label);
    }
}
