<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Exception\File;

class DuplicateFileException extends \Exception
{
    public function __construct(
        public readonly ?string $filename,
    ) {
        parent::__construct('Duplicate file: ' . $filename);
    }
}
