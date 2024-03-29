<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Services;

class DataRepository
{
    private static ?\PDO $connection = null;

    public function __construct(
        private readonly string $databaseDsn,
    ) {
        self::$connection = null;
    }

    /**
     * @param non-empty-string[] $tableNames
     */
    public function removeAllFor(array $tableNames): void
    {
        foreach ($tableNames as $tableName) {
            $this->getConnection()->query('TRUNCATE TABLE ' . $tableName . ' CASCADE');
        }
    }

    public function getConnection(): \PDO
    {
        if (null === self::$connection) {
            self::$connection = new \PDO($this->databaseDsn);
        }

        return self::$connection;
    }
}
