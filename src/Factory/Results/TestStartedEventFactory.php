<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Factory\Results;

use SmartAssert\ApiClient\Data\Results\EventInterface;
use SmartAssert\ApiClient\Data\Results\TestStartedEvent;
use SmartAssert\ApiClient\Factory\AbstractFactory;
use webignition\BasilModels\Parser\Exception\InvalidTestException;
use webignition\BasilModels\Parser\Exception\UnparseableTestException;
use webignition\BasilModels\Parser\Test\TestParser;

readonly class TestStartedEventFactory extends AbstractFactory
{
    public function __construct(
        private TestParser $testParser,
    ) {}

    /**
     * @throws InvalidTestException
     * @throws UnparseableTestException
     */
    public function create(EventInterface $event): TestStartedEvent
    {
        $bodyData = $event->getBody();
        $documentData = $bodyData['document'] ?? [];
        $documentData = is_array($documentData) ? $documentData : [];

        $modelData = $documentData['payload'] ?? [];
        $modelData = is_array($modelData) ? $modelData : [];

        return new TestStartedEvent($event, $this->testParser->parse($modelData));
    }
}
