<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Factory\JobCoordinator;

use SmartAssert\ApiClient\Data\JobCoordinator\Job\Preparation;
use SmartAssert\ApiClient\Exception\IncompleteDataException;
use SmartAssert\ApiClient\Factory\AbstractFactory;

readonly class PreparationFactory extends AbstractFactory
{
    public function __construct(
        private MetaStateFactory $metaStateFactory,
    ) {}

    /**
     * @param array<mixed> $data
     *
     * @throws IncompleteDataException
     */
    public function create(array $data): Preparation
    {
        return new Preparation(
            $this->getNonEmptyString($data, 'state'),
            $this->metaStateFactory->create($data),
        );
    }
}
