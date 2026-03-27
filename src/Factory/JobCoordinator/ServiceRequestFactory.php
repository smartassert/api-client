<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Factory\JobCoordinator;

use SmartAssert\ApiClient\Data\JobCoordinator\Job\ServiceRequest;
use SmartAssert\ApiClient\Data\JobCoordinator\Job\ServiceRequestAttempt;
use SmartAssert\ApiClient\Exception\IncompleteDataException;
use SmartAssert\ApiClient\Factory\AbstractFactory;

readonly class ServiceRequestFactory extends AbstractFactory
{
    /**
     * @param array<mixed> $data
     *
     * @return ServiceRequest[]
     *
     * @throws IncompleteDataException
     */
    public function createCollection(array $data): array
    {
        $serviceRequests = [];

        foreach ($data as $serviceRequestIndex => $serviceRequestData) {
            if (is_array($serviceRequestData)) {
                try {
                    $serviceRequests[] = $this->create($serviceRequestData);
                } catch (IncompleteDataException $e) {
                    throw new IncompleteDataException($data, $serviceRequestIndex . '.' . $e->missingKey);
                }
            }
        }

        return $serviceRequests;
    }

    /**
     * @param array<mixed> $data
     *
     * @throws IncompleteDataException
     */
    private function create(array $data): ServiceRequest
    {
        $type = $this->getNonEmptyString($data, 'type');

        $attemptsData = $data['attempts'] ?? null;
        $attemptsData = is_array($attemptsData) ? $attemptsData : null;
        if (null === $attemptsData) {
            throw new IncompleteDataException($data, 'attempts');
        }

        try {
            $attempts = $this->createServiceRequestAttempts($attemptsData);
        } catch (IncompleteDataException $e) {
            throw new IncompleteDataException($data, 'attempts.' . $e->missingKey);
        }

        return new ServiceRequest($type, $attempts);
    }

    /**
     * @param array<mixed> $data
     *
     * @return ServiceRequestAttempt[]
     *
     * @throws IncompleteDataException
     */
    private function createServiceRequestAttempts(array $data): array
    {
        $attempts = [];

        foreach ($data as $attemptData) {
            if (is_array($attemptData)) {
                $attempts[] = new ServiceRequestAttempt($this->getNonEmptyString($attemptData, 'state'));
            }
        }

        return $attempts;
    }
}
