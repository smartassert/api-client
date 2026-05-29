<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Factory\JobCoordinator;

use SmartAssert\ApiClient\Data\JobCoordinator\Job\MetaState;

readonly class MetaStateFactory
{
    /**
     * @param array<mixed> $data
     */
    public function create(array $data): MetaState
    {
        $metaStateData = $data['meta_state'] ?? null;
        $metaStateData = is_array($metaStateData) ? $metaStateData : null;

        if (null === $metaStateData) {
            return new MetaState(ended: false, succeeded: false, pending: true);
        }

        $ended = $metaStateData['ended'] ?? false;
        $ended = is_bool($ended) ? $ended : false;

        $succeeded = $metaStateData['succeeded'] ?? false;
        $succeeded = is_bool($succeeded) ? $succeeded : false;

        $pending = $metaStateData['pending'] ?? true;
        $pending = is_bool($pending) ? $pending : true;

        return new MetaState(ended: $ended, succeeded: $succeeded, pending: $pending);
    }
}
