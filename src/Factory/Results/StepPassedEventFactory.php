<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Factory\Results;

use SmartAssert\ApiClient\Data\Results\EventInterface;
use SmartAssert\ApiClient\Data\Results\Statement;
use SmartAssert\ApiClient\Data\Results\StatementCollection;
use SmartAssert\ApiClient\Data\Results\StatementInterface;
use SmartAssert\ApiClient\Data\Results\Step;
use SmartAssert\ApiClient\Data\Results\StepPassedEvent;
use SmartAssert\ApiClient\Factory\AbstractFactory;

readonly class StepPassedEventFactory extends AbstractFactory
{
    public function create(EventInterface $event): StepPassedEvent
    {
        $bodyData = $event->getBody();
        $documentData = $bodyData['document'] ?? [];
        $documentData = is_array($documentData) ? $documentData : [];

        $modelData = $documentData['payload'] ?? [];
        $modelData = is_array($modelData) ? $modelData : [];

        $name = $modelData['name'] ?? '';
        $name = is_string($name) ? $name : '';

        $status = $modelData['status'] ?? 'failed';
        $status = in_array($status, ['passed', 'failed'], true) ? $status : 'failed';

        $statementsData = $modelData['statements'] ?? [];
        $statementsData = is_array($statementsData) ? $statementsData : [];

        $statements = [];

        foreach ($statementsData as $statementData) {
            if (!is_array($statementData)) {
                continue;
            }

            $statement = $this->createStatement($statementData);
            if (null === $statement) {
                continue;
            }

            $statements[] = $statement;
        }

        $step = new Step($name, $status, new StatementCollection($statements));

        return new StepPassedEvent($event, $step);
    }

    /**
     * @param array<mixed> $data
     */
    private function createStatement(array $data): ?StatementInterface
    {
        $type = $data['type'] ?? null;
        $type = is_string($type) ? $type : null;
        if ('action' !== $type && 'assertion' !== $type) {
            return null;
        }

        $status = $data['status'] ?? null;
        $status = is_string($status) ? $status : null;
        if ('passed' !== $status && 'failed' !== $status) {
            return null;
        }

        $source = $data['source'] ?? '';
        $source = is_string($source) ? $source : '';

        return new Statement($type, $source, $status);
    }
}
