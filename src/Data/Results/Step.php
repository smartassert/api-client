<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Data\Results;

readonly class Step implements StepInterface
{
    /**
     * @param 'failed'|'passed' $status
     */
    public function __construct(
        private string $name,
        private string $status,
        private StatementCollectionInterface $statements,
    ) {}

    public function getName(): string
    {
        return $this->name;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getStatements(): StatementCollectionInterface
    {
        return $this->statements;
    }
}
