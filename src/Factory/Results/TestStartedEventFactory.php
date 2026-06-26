<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Factory\Results;

use SmartAssert\ApiClient\Data\Results\EventInterface;
use SmartAssert\ApiClient\Data\Results\Test;
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

        $path = $modelData['path'] ?? '';
        $path = is_string($path) ? $path : '';

        $testModel = $this->testParser->parse($modelData);
        $test = new Test($path, $testModel->getBrowser(), $testModel->getUrl());

        return new TestStartedEvent($event, $test);
    }
}
