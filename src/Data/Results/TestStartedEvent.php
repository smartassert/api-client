<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Data\Results;

readonly class TestStartedEvent extends AbstractEncapsulatingEvent implements EventInterface, HasTestReferenceInterface
{
    public function getTestReference(): ResourceReference
    {
        return $this->getResourceReference();
    }

    public function getStepReferences(): ResourceReferenceCollection
    {
        return $this->getRelatedReferences() ?? new ResourceReferenceCollection();
    }

    public function getConfiguration(): TestConfigurationInterface
    {
        $bodyData = $this->getBody();

        $documentData = $bodyData['document'] ?? [];
        $documentData = is_array($documentData) ? $documentData : [];

        $payloadData = $documentData['payload'] ?? [];
        $payloadData = is_array($payloadData) ? $payloadData : [];

        $configurationData = $payloadData['config'] ?? [];
        $configurationData = is_array($configurationData) ? $configurationData : [];

        $browser = $configurationData['browser'] ?? '';
        $browser = is_string($browser) ? $browser : '';

        $url = $configurationData['url'] ?? '';
        $url = is_string($url) ? $url : '';

        return new TestConfiguration($browser, $url);
    }
}
