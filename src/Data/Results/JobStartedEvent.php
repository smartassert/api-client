<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Data\Results;

readonly class JobStartedEvent extends AbstractEncapsulatingEvent implements EventInterface
{
    /**
     * @return TestInterface[]
     */
    public function getTests(): array
    {
        $testNames = $this->createTestNameList();
        $tests = [];

        foreach ($testNames as $testName) {
            $resourceReference = $this->getRelatedReferences()?->getForLabel($testName);

            if (null !== $resourceReference) {
                $tests[] = new Test($resourceReference);
            }
        }

        return $tests;
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
