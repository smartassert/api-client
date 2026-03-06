<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Data\JobCoordinator\Job;

/**
 * @implements \IteratorAggregate<string, IsComponentInterface>
 */
readonly class Components implements \IteratorAggregate
{
    /**
     * @param array<string, IsComponentInterface> $components
     */
    public function __construct(
        public array $components,
    ) {}

    public function get(string $name): ?IsComponentInterface
    {
        return $this->components[$name] ?? null;
    }

    public function filterByMetaState(MetaState $metaState): self
    {
        $components = [];

        foreach ($this->components as $name => $component) {
            if ($this->componentMatchesMetaState($component, $metaState)) {
                $components[$name] = $component;
            }
        }

        return new Components($components);
    }

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->components);
    }

    private function componentMatchesMetaState(IsComponentInterface $component, MetaState $metaState): bool
    {
        return $component->getMetaState()->ended === $metaState->ended
            && $component->getMetaState()->succeeded === $metaState->succeeded;
    }
}
