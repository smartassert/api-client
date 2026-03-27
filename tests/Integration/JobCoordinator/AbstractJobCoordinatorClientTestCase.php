<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\JobCoordinator;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\HttpFactory;
use SmartAssert\ApiClient\Exception\Error\Factory as ExceptionFactory;
use SmartAssert\ApiClient\Factory\JobCoordinator\JobFactory;
use SmartAssert\ApiClient\Factory\JobCoordinator\MachineFactory;
use SmartAssert\ApiClient\Factory\JobCoordinator\MetaStateFactory;
use SmartAssert\ApiClient\Factory\JobCoordinator\PreparationFactory;
use SmartAssert\ApiClient\Factory\JobCoordinator\ResultsJobFactory;
use SmartAssert\ApiClient\Factory\JobCoordinator\SerializedSuiteFactory;
use SmartAssert\ApiClient\Factory\JobCoordinator\ServiceRequestFactory;
use SmartAssert\ApiClient\Factory\JobCoordinator\SummaryFactory;
use SmartAssert\ApiClient\Factory\JobCoordinator\WorkerJobFactory;
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
                new SerializedSuiteFactory($metaStateFactory),
                new MachineFactory($metaStateFactory),
                new WorkerJobFactory($metaStateFactory),
                new ServiceRequestFactory(),
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
