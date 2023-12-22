<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Data\Source;

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

    /**
     * @return non-empty-string
     */
    public function getType(): string;
}
