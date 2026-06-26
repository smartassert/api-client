<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Data\Results;

interface StepInterface
{
    public function getName(): string;

    /**
     * @return 'failed'|'passed'
     */
    public function getStatus(): string;

    public function getStatements(): StatementCollectionInterface;
}
