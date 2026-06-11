<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Factory\Results;

use SmartAssert\ApiClient\Data\Results\ResourceReferenceCollection;
use SmartAssert\ApiClient\Exception\Factory\IncompleteDataException;
use SmartAssert\ApiClient\Factory\AbstractFactory;

readonly class ResourceReferenceCollectionFactory extends AbstractFactory
{
    public function __construct(
        private ResourceReferenceFactory $resourceReferenceFactory,
    ) {}

    /**
     * @param array<mixed> $data
     *
     * @throws IncompleteDataException
     */
    public function create(array $data): ?ResourceReferenceCollection
    {
        if ([] === $data) {
            return null;
        }

        $references = [];

        foreach ($data as $referenceIndex => $referenceData) {
            if (!is_array($referenceData)) {
                continue;
            }

            try {
                $references[] = $this->resourceReferenceFactory->create($referenceData);
            } catch (IncompleteDataException $e) {
                throw new IncompleteDataException(
                    $data,
                    'related_reference[' . $referenceIndex . '].' . $e->missingKey
                );
            }
        }

        return new ResourceReferenceCollection($references);
    }
}
