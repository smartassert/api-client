<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Services;

class SuitesRepository extends DataRepository
{
    public function __construct()
    {
        parent::__construct('pgsql:host=localhost;port=5432;dbname=sources;user=postgres;password=password!');
    }

    public function removeAllSuites(): void
    {
        $this->removeAllFor(['suite']);
    }
}
