<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\Source;

use GuzzleHttp\Client as HttpClient;
use SmartAssert\ApiClient\Factory\Source\SourceFactory;
use SmartAssert\ApiClient\ServiceClient\HttpHandler;
use SmartAssert\ApiClient\SourceClient;
use SmartAssert\ApiClient\Tests\Integration\AbstractIntegrationTestCase;

abstract class AbstractSourceTestCase extends AbstractIntegrationTestCase
{
    protected static SourceClient $sourceClient;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$sourceClient = new SourceClient(
            self::$urlGenerator,
            new SourceFactory(),
            new HttpHandler(
                new HttpClient()
            )
        );
    }
}
