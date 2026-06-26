<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Data\Results;

interface StatementInterface
{
    /**
     * @return 'action'|'assertion'
     */
    public function getType(): string;

    public function getSource(): string;

    /**
     * @return 'failed'|'passed'
     */
    public function getStatus(): string;
}
