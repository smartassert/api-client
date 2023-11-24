<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Model\Source;

readonly class GitSource extends AbstractSource
{
    /**
     * @param non-empty-string $id
     * @param non-empty-string $label
     * @param non-empty-string $hostUrl
     * @param non-empty-string $path
     * @param ?positive-int    $deletedAt
     */
    public function __construct(
        string $id,
        string $label,
        public string $hostUrl,
        public string $path,
        public bool $hasCredentials,
        ?int $deletedAt,
    ) {
        parent::__construct($id, $label, $deletedAt);
    }

    public function getType(): string
    {
        return 'git';
    }
}
