<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Factory\Source;

use SmartAssert\ApiClient\Data\Source\Suite;
use SmartAssert\ApiClient\Exception\IncompleteDataException;
use SmartAssert\ApiClient\Factory\AbstractFactory;

readonly class SuiteFactory extends AbstractFactory
{
    /**
     * @param array<mixed> $data
     *
     * @throws IncompleteDataException
     */
    public function create(array $data): Suite
    {
        return new Suite(
            $this->getNonEmptyString($data, 'id'),
            $this->getNonEmptyString($data, 'source_id'),
            $this->getNonEmptyString($data, 'label'),
            $this->getTests($data),
            $this->getEntityDeletedAt($data),
        );
    }

    /**
     * @param array<mixed> $data
     *
     * @return string[]
     */
    private function getTests(array $data): array
    {
        $tests = $data['tests'] ?? [];
        $tests = is_array($tests) ? $tests : [];

        $filteredTests = [];
        foreach ($tests as $test) {
            if (is_string($test)) {
                $filteredTests[] = $test;
            }
        }

        sort($filteredTests);

        return $filteredTests;
    }
}
