<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Factory\JobCoordinator;

use SmartAssert\ApiClient\Data\JobCoordinator\Job\WorkerJob;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\WorkerJobComponent;
use SmartAssert\ApiClient\Exception\IncompleteDataException;
use SmartAssert\ApiClient\Factory\AbstractFactory;

readonly class WorkerJobFactory extends AbstractFactory
{
    public function __construct(
        private MetaStateFactory $metaStateFactory,
        private WorkerJobCreationFailureFactory $workerJobCreationFailureFactory,
    ) {}

    /**
     * @param array<mixed> $data
     *
     * @throws IncompleteDataException
     */
    public function create(array $data): WorkerJob
    {
        $state = $this->getNonEmptyString($data, 'state');

        $componentDataCollection = $data['components'] ?? [];
        $componentDataCollection = is_array($componentDataCollection) ? $componentDataCollection : [];

        $components = [];

        foreach ($componentDataCollection as $componentName => $componentData) {
            if (is_string($componentName) && '' !== $componentName && is_array($componentData)) {
                try {
                    $components[$componentName] = new WorkerJobComponent(
                        $this->getNonEmptyString($componentData, 'state'),
                        $this->metaStateFactory->create($componentData),
                    );
                } catch (IncompleteDataException $e) {
                    throw new IncompleteDataException($data, 'components.' . $componentName . '.' . $e->missingKey);
                }
            }
        }

        $creationFailureData = $data['creation_failure'] ?? null;
        $creationFailureData = is_array($creationFailureData) ? $creationFailureData : null;

        $creationFailure = null;
        if (is_array($creationFailureData)) {
            $creationFailure = $this->workerJobCreationFailureFactory->create($creationFailureData);
        }

        return new WorkerJob($state, $this->metaStateFactory->create($data), $components, $creationFailure);
    }
}
