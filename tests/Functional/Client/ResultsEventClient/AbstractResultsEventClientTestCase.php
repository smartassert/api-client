<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Functional\Client\ResultsEventClient;

use GuzzleHttp\Psr7\HttpFactory;
use SmartAssert\ApiClient\Factory\Results\FactoryFactory;
use SmartAssert\ApiClient\ResultsEventClient;
use SmartAssert\ApiClient\ServiceClient\HttpHandler;
use SmartAssert\ApiClient\Tests\Functional\Client\AbstractClientTestCase;
use SmartAssert\ApiClient\Tests\Functional\DataProvider\CommonNonSuccessResponseDataProviderTrait;
use SmartAssert\ApiClient\UrlGeneratorFactory;

abstract class AbstractResultsEventClientTestCase extends AbstractClientTestCase
{
    use CommonNonSuccessResponseDataProviderTrait;

    protected const string JOB_LABEL = 'job_label';

    protected ResultsEventClient $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = new ResultsEventClient(
            FactoryFactory::createEventFactory(),
            new HttpHandler(
                $this->httpClient,
                $this->exceptionFactory,
                new HttpFactory(),
                UrlGeneratorFactory::create('https://api.example.com'),
            ),
        );
    }

    protected function getExpectedAuthorizationHeader(): string
    {
        return 'Bearer ' . self::API_KEY;
    }
}
