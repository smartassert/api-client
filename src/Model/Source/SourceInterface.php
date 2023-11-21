<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Model\Source;

interface SourceInterface
{
    /**
     * @return non-empty-string
     */
    public function getId(): string;

    /**
     * @return non-empty-string
     */
    public function getLabel(): string;

    /**
     * @return ?positive-int
     */
    public function getDeletedAt(): ?int;
}
