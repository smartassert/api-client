<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Factory\Source;

use SmartAssert\ApiClient\Data\Source\SerializedSuite;
use SmartAssert\ApiClient\Exception\IncompleteDataException;
use SmartAssert\ApiClient\Factory\AbstractFactory;

readonly class SerializedSuiteFactory extends AbstractFactory
{
    /**
     * @param array<mixed> $data
     *
     * @throws IncompleteDataException
     */
    public function create(array $data): SerializedSuite
    {
        return new SerializedSuite(
            $this->getNonEmptyString($data, 'id'),
            $this->getNonEmptyString($data, 'suite_id'),
            $this->getNonEmptyString($data, 'state'),
            $this->getParameters($data),
        );
    }

    /**
     * @param array<mixed> $data
     *
     * @return array<string, scalar>
     */
    private function getParameters(array $data): array
    {
        $parameters = $data['parameters'] ?? [];
        $parameters = is_array($parameters) ? $parameters : [];

        $filteredParameters = [];

        foreach ($parameters as $key => $value) {
            if (is_string($key) && is_scalar($value)) {
                $filteredParameters[$key] = $value;
            }
        }

        return $filteredParameters;
    }
}
