<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Functional\Client\SuiteClient;

use GuzzleHttp\Psr7\HttpFactory;
use SmartAssert\ApiClient\Factory\Source\SuiteFactory;
use SmartAssert\ApiClient\ServiceClient\HttpHandler;
use SmartAssert\ApiClient\SuiteClient;
use SmartAssert\ApiClient\Tests\Functional\Client\AbstractClientTestCase;
use SmartAssert\ApiClient\Tests\Functional\DataProvider\CommonNonSuccessResponseDataProviderTrait;
use SmartAssert\ApiClient\UrlGeneratorFactory;

abstract class AbstractSuiteClientTestCase extends AbstractClientTestCase
{
    use CommonNonSuccessResponseDataProviderTrait;

    protected const ID = 'id';
    protected const SOURCE_ID = 'source_id';
    protected const LABEL = 'label';

    protected SuiteClient $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = new SuiteClient(
            new SuiteFactory(),
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
