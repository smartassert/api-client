<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\GitSource;

use SmartAssert\ApiClient\Factory\Source\SourceFactory;
use SmartAssert\ApiClient\GitSourceClient;
use SmartAssert\ApiClient\Tests\Integration\AbstractIntegrationTestCase;

abstract class AbstractGitSourceTestCase extends AbstractIntegrationTestCase
{
    protected static GitSourceClient $gitSourceClient;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$gitSourceClient = new GitSourceClient(
            self::$urlGenerator,
            self::createServiceClient(),
            new SourceFactory(),
        );
    }
}
