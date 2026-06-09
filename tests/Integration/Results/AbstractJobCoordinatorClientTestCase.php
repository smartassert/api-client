<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\Results;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\HttpFactory;
use SmartAssert\ApiClient\Exception\Error\Factory as ExceptionFactory;
use SmartAssert\ApiClient\Factory\Results\FactoryFactory;
use SmartAssert\ApiClient\ResultsEventClient;
use SmartAssert\ApiClient\ServiceClient\HttpHandler;
use SmartAssert\ApiClient\Tests\Integration\AbstractIntegrationTestCase;

class AbstractJobCoordinatorClientTestCase extends AbstractIntegrationTestCase
{
    protected ResultsEventClient $resultsEventClient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resultsEventClient = new ResultsEventClient(
            FactoryFactory::createEventFactory(),
            new HttpHandler(
                new HttpClient(),
                new ExceptionFactory(self::$errorDeserializer),
                new HttpFactory(),
                self::$urlGenerator,
            ),
        );
    }
}
