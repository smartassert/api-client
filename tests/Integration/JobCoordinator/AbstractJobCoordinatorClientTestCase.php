<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\JobCoordinator;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\HttpFactory;
use SmartAssert\ApiClient\Exception\Error\Factory as ExceptionFactory;
use SmartAssert\ApiClient\Factory\JobCoordinator\JobFactory;
use SmartAssert\ApiClient\Factory\JobCoordinator\SummaryFactory;
use SmartAssert\ApiClient\JobCoordinatorClient;
use SmartAssert\ApiClient\ServiceClient\HttpHandler;
use SmartAssert\ApiClient\Tests\Integration\AbstractIntegrationTestCase;

class AbstractJobCoordinatorClientTestCase extends AbstractIntegrationTestCase
{
    protected JobCoordinatorClient $jobCoordinatorClient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->jobCoordinatorClient = new JobCoordinatorClient(
            new JobFactory(
                new SummaryFactory(),
            ),
            new SummaryFactory(),
            new HttpHandler(
                new HttpClient(),
                new ExceptionFactory(self::$errorDeserializer),
                new HttpFactory(),
                self::$urlGenerator,
            ),
        );
    }
}
