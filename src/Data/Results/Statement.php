<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Data\Results;

readonly class Statement implements StatementInterface
{
    /**
     * @param 'action'|'assertion' $type
     * @param 'failed'|'passed'    $status
     */
    public function __construct(
        private string $type,
        private string $source,
        private string $status,
    ) {}

    public function getType(): string
    {
        return $this->type;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function getStatus(): string
    {
        return $this->status;
    }
}
