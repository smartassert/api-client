<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\SerializedSuite;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\HttpFactory;
use SmartAssert\ApiClient\Exception\Error\Factory as ExceptionFactory;
use SmartAssert\ApiClient\Factory\Source\SerializedSuiteFactory;
use SmartAssert\ApiClient\SerializedSuiteClient;
use SmartAssert\ApiClient\ServiceClient\HttpHandler;
use SmartAssert\ApiClient\Tests\Integration\AbstractIntegrationTestCase;

abstract class AbstractSerializedSuiteTestCase extends AbstractIntegrationTestCase
{
    protected static SerializedSuiteClient $serializedSuiteClient;

    protected function setUp(): void
    {
        parent::setUp();

        self::$serializedSuiteClient = new SerializedSuiteClient(
            new SerializedSuiteFactory(),
            new HttpHandler(
                new HttpClient(),
                new ExceptionFactory(self::$errorDeserializer),
                new HttpFactory(),
                self::$urlGenerator,
            ),
        );
    }
}
