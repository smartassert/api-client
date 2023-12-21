<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\File;

use GuzzleHttp\Client as HttpClient;
use SmartAssert\ApiClient\FileClient;
use SmartAssert\ApiClient\ServiceClient\HttpHandler;
use SmartAssert\ApiClient\Tests\Integration\AbstractIntegrationTestCase;

abstract class AbstractFileTestCase extends AbstractIntegrationTestCase
{
    protected static FileClient $fileClient;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$fileClient = new FileClient(
            self::$fooUrlGenerator,
            new HttpHandler(
                new HttpClient(),
            )
        );
    }
}
