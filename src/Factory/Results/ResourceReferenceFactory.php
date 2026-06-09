<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Factory\Results;

use SmartAssert\ApiClient\Data\Results\ResourceReference;
use SmartAssert\ApiClient\Exception\IncompleteDataException;
use SmartAssert\ApiClient\Factory\AbstractFactory;

readonly class ResourceReferenceFactory extends AbstractFactory
{
    /**
     * @param array<mixed> $data
     *
     * @throws IncompleteDataException
     */
    public function create(array $data): ResourceReference
    {
        return new ResourceReference(
            $this->getNonEmptyString($data, 'label'),
            $this->getNonEmptyString($data, 'reference'),
        );
    }
}
