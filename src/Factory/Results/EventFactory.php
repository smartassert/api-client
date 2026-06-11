<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Factory\Results;

use SmartAssert\ApiClient\Data\Results\Event;
use SmartAssert\ApiClient\Exception\Factory\IncompleteDataException;
use SmartAssert\ApiClient\Factory\AbstractFactory;

readonly class EventFactory extends AbstractFactory
{
    public function __construct(
        private ResourceReferenceFactory $resourceReferenceFactory,
        private ResourceReferenceCollectionFactory $resourceReferenceCollectionFactory,
    ) {}

    /**
     * @param array<mixed> $data
     *
     * @throws IncompleteDataException
     */
    public function create(array $data): Event
    {
        $sequenceNumber = $data['sequence_number'] ?? null;
        $sequenceNumber = is_int($sequenceNumber) ? $sequenceNumber : null;
        $sequenceNumber = $sequenceNumber > 0 ? $sequenceNumber : null;

        if (null === $sequenceNumber) {
            throw new IncompleteDataException($data, 'sequence_number');
        }

        $body = $data['body'] ?? null;
        $body = is_array($body) ? $body : [];

        $relatedReferencesData = $data['related_references'] ?? [];
        $relatedReferencesData = is_array($relatedReferencesData) ? $relatedReferencesData : [];

        return new Event(
            $sequenceNumber,
            $this->getNonEmptyString($data, 'type'),
            $this->resourceReferenceFactory->create($data),
            $body,
            $this->resourceReferenceCollectionFactory->create($relatedReferencesData),
        );
    }
}
