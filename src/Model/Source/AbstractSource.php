<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Model\Source;

abstract readonly class AbstractSource implements SourceInterface
{
    /**
     * @param non-empty-string $id
     * @param non-empty-string $label
     * @param ?positive-int    $deletedAt
     */
    public function __construct(
        public string $id,
        public string $label,
        public ?int $deletedAt,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getDeletedAt(): ?int
    {
        return $this->deletedAt;
    }
}
