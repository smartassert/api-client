<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\User;

use SmartAssert\ApiClient\Tests\Integration\AbstractIntegrationTestCase;
use SmartAssert\ApiClient\UsersClient;

abstract class AbstractUserTestCase extends AbstractIntegrationTestCase
{
    protected static UsersClient $client;

    public static function setUpBeforeClass(): void
    {
        self::$client = new UsersClient(
            'http://localhost:9081',
            self::createServiceClient(),
        );
    }
}
