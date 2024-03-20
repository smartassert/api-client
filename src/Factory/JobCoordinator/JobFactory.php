<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Factory\JobCoordinator;

use SmartAssert\ApiClient\Data\JobCoordinator\Job\Job;
use SmartAssert\ApiClient\Exception\IncompleteDataException;
use SmartAssert\ApiClient\Factory\AbstractFactory;

readonly class JobFactory extends AbstractFactory
{
    /**
     * @param array<mixed> $data
     *
     * @throws IncompleteDataException
     */
    public function create(array $data): Job
    {
        $id = $this->getNonEmptyString($data, 'id');
        $suiteId = $this->getNonEmptyString($data, 'suite_id');

        $maximumDurationInSeconds = $data['maximum_duration_in_seconds'] ?? 0;
        $maximumDurationInSeconds = is_int($maximumDurationInSeconds) ? $maximumDurationInSeconds : 0;
        if ($maximumDurationInSeconds < 1) {
            throw new IncompleteDataException($data, 'maximum_duration_in_seconds');
        }

        return new Job($id, $suiteId, $maximumDurationInSeconds);
    }
}
