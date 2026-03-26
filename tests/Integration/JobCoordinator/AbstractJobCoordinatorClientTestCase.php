<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\JobCoordinator;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\HttpFactory;
use SmartAssert\ApiClient\Exception\Error\Factory as ExceptionFactory;
use SmartAssert\ApiClient\Factory\JobCoordinator\Job\JobFactory;
use SmartAssert\ApiClient\Factory\JobCoordinator\Job\MetaStateFactory;
use SmartAssert\ApiClient\Factory\JobCoordinator\Job\PreparationFactory;
use SmartAssert\ApiClient\Factory\JobCoordinator\Job\ResultsJobFactory;
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

        $metaStateFactory = new MetaStateFactory();

        $this->jobCoordinatorClient = new JobCoordinatorClient(
            new JobFactory(
                new SummaryFactory(),
                $metaStateFactory,
                new ResultsJobFactory($metaStateFactory),
                new PreparationFactory($metaStateFactory),
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
