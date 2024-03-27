<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Data\JobCoordinator\Job;

use Symfony\Component\Uid\Ulid;

readonly class Summary
{
    /**
     * @param non-empty-string $id
     * @param non-empty-string $suiteId
     * @param positive-int     $maximumDurationInSeconds
     */
    public function __construct(
        public string $id,
        public string $suiteId,
        public int $maximumDurationInSeconds,
    ) {
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return (new Ulid($this->id))->getDateTime();
    }
}
