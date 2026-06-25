<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Data\Results;

use SmartAssert\ApiClient\Data\Results\AbstractEncapsulatingEvent as BaseEvent;

readonly class JobStartedEvent extends BaseEvent implements EventInterface, HasJobReferenceInterface
{
    public function getJobReference(): ResourceReference
    {
        return $this->getResourceReference();
    }

    public function getTestReferences(): ResourceReferenceCollection
    {
        $testNames = $this->createTestNameList();
        $resourceReferences = [];

        foreach ($testNames as $testName) {
            $resourceReference = $this->getRelatedReferences()?->getForLabel($testName);

            if (null !== $resourceReference) {
                $resourceReferences[] = $resourceReference;
            }
        }

        return new ResourceReferenceCollection($resourceReferences);
    }

    /**
     * @return non-empty-string[]
     */
    private function createTestNameList(): array
    {
        $bodyData = $this->getBody();
        $testNames = $bodyData['tests'] ?? [];
        $testNames = is_array($testNames) ? $testNames : [];

        $filteredTestNames = [];

        foreach ($testNames as $testName) {
            if (!is_string($testName) || '' === $testName) {
                continue;
            }

            $filteredTestNames[] = $testName;
        }

        return $filteredTestNames;
    }
}
