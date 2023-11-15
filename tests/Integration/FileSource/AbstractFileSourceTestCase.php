<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\FileSource;

use SmartAssert\ApiClient\FileSourceClient;
use SmartAssert\ApiClient\Tests\Integration\AbstractIntegrationTestCase;

abstract class AbstractFileSourceTestCase extends AbstractIntegrationTestCase
{
    protected static FileSourceClient $fileSourceClient;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$fileSourceClient = new FileSourceClient(self::$urlGenerator, self::createServiceClient());
    }
}
