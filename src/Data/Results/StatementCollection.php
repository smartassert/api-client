<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Data\Results;

/**
 * @implements \IteratorAggregate<StatementInterface>
 */
readonly class StatementCollection implements StatementCollectionInterface, \IteratorAggregate
{
    /**
     * @param StatementInterface[] $statements
     */
    public function __construct(
        private array $statements,
    ) {}

    public function getIterator(): \Traversable
    {
        return new \ArrayIterator($this->statements);
    }
}
