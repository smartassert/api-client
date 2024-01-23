<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\File;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\HttpFactory;
use SmartAssert\ApiClient\Exception\Error\Factory as ExceptionFactory;
use SmartAssert\ApiClient\FileClient;
use SmartAssert\ApiClient\RequestBuilder\RequestBuilder;
use SmartAssert\ApiClient\ServiceClient\HttpHandler;
use SmartAssert\ApiClient\Tests\Integration\AbstractIntegrationTestCase;

abstract class AbstractFileTestCase extends AbstractIntegrationTestCase
{
    protected static FileClient $fileClient;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$fileClient = new FileClient(
            new HttpHandler(
                new HttpClient(),
                new ExceptionFactory(self::$errorDeserializer),
            ),
            new RequestBuilder(
                new HttpFactory(),
                self::$urlGenerator,
            ),
        );
    }
}
