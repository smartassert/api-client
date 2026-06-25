<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Factory\Results;

use SmartAssert\ApiClient\Data\Results\CompilationPassedEvent;
use SmartAssert\ApiClient\Data\Results\CompilationStartedEvent;
use SmartAssert\ApiClient\Data\Results\Event;
use SmartAssert\ApiClient\Data\Results\EventInterface;
use SmartAssert\ApiClient\Data\Results\JobStartedEvent;
use SmartAssert\ApiClient\Data\Results\LifecycleEvent;
use SmartAssert\ApiClient\Data\Results\TestStartedEvent;
use SmartAssert\ApiClient\Exception\Factory\IncompleteDataException;
use SmartAssert\ApiClient\Factory\AbstractFactory;
use webignition\BasilModels\Parser\Exception\InvalidTestException;
use webignition\BasilModels\Parser\Exception\UnparseableTestException;
use webignition\BasilModels\Parser\Test\TestParser;

readonly class EventFactory extends AbstractFactory
{
    public function __construct(
        private ResourceReferenceFactory $resourceReferenceFactory,
        private ResourceReferenceCollectionFactory $resourceReferenceCollectionFactory,
        private TestParser $testParser,
    ) {}

    /**
     * @param array<mixed> $data
     *
     * @throws IncompleteDataException
     * @throws InvalidTestException
     * @throws UnparseableTestException
     */
    public function create(array $data): EventInterface
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

        $type = $this->getNonEmptyString($data, 'type');

        $event = new Event(
            $sequenceNumber,
            $type,
            $this->resourceReferenceFactory->create($data),
            $body,
            $this->resourceReferenceCollectionFactory->create($relatedReferencesData),
        );

        if ('job/started' === $type) {
            $event = new JobStartedEvent($event);
        }

        if (
            'lifecycle/compilation-started' === $type
            || 'lifecycle/compilation-completed' === $type
            || 'lifecycle/execution-started' === $type
            || 'lifecycle/execution-completed' === $type
        ) {
            $event = new LifecycleEvent($event);
        }

        if ('compilation/started' === $type) {
            $event = new CompilationStartedEvent($event);
        }

        if ('compilation/passed' === $type) {
            $event = new CompilationPassedEvent($event);
        }

        if ('test/started' === $type) {
            $bodyData = $event->getBody();
            $documentData = $bodyData['document'] ?? [];
            $documentData = is_array($documentData) ? $documentData : [];

            $testModelData = $documentData['payload'] ?? [];
            $testModelData = is_array($testModelData) ? $testModelData : [];

            $test = $this->testParser->parse($testModelData);
            $event = new TestStartedEvent($event, $test);
        }

        return $event;
    }
}
