<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Data\Results;

readonly class TestStartedEvent extends AbstractEncapsulatingEvent implements EventInterface
{
    public function getTest(): TestInterface
    {
        return new Test($this->getResourceReference());
    }

    /**
     * @return StepMetadataInterface[]
     */
    public function getStepMetadataCollection(): array
    {
        $steps = [];

        $relatedReferences = $this->getRelatedReferences() ?? [];

        foreach ($relatedReferences as $relatedReference) {
            $steps[] = new StepMetadata($relatedReference);
        }

        return $steps;
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
