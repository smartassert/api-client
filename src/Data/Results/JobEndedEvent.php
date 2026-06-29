<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Data\Results;

use SmartAssert\ApiClient\Data\Results\AbstractEncapsulatingEvent as BaseEvent;

readonly class JobEndedEvent extends BaseEvent implements EventInterface, HasJobReferenceInterface
{
    public function getJobReference(): ResourceReference
    {
        return $this->getResourceReference();
    }

    public function getEndState(): ?string
    {
        $bodyData = $this->getBody();
        $endState = $bodyData['end_state'] ?? null;

        return is_string($endState) ? $endState : null;
    }

    public function getIsSuccess(): bool
    {
        $bodyData = $this->getBody();
        $success = $bodyData['success'] ?? false;

        return true === $success;
    }

    public function getEventCount(): ?int
    {
        $bodyData = $this->getBody();
        $eventCount = $bodyData['event_count'] ?? null;

        return is_int($eventCount) ? $eventCount : null;
    }
}
