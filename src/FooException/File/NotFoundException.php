<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\FooException\File;

class NotFoundException extends \Exception
{
    public function __construct(
        public readonly ?string $filename,
    ) {
        parent::__construct('Not found: ' . $filename);
    }
}
