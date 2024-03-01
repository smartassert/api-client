<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Functional\Client\SerializedSuiteClient;

use GuzzleHttp\Psr7\HttpFactory;
use SmartAssert\ApiClient\Factory\Source\SerializedSuiteFactory;
use SmartAssert\ApiClient\SerializedSuiteClient;
use SmartAssert\ApiClient\ServiceClient\HttpHandler;
use SmartAssert\ApiClient\Tests\Functional\Client\AbstractClientTestCase;
use SmartAssert\ApiClient\Tests\Functional\DataProvider\CommonNonSuccessResponseDataProviderTrait;
use SmartAssert\ApiClient\UrlGeneratorFactory;

abstract class AbstractSerializedSuiteClientTestCase extends AbstractClientTestCase
{
    use CommonNonSuccessResponseDataProviderTrait;

    protected const ID = 'id';
    protected const SUITE_ID = 'suite_id';
    protected const STATE = 'requested';

    protected SerializedSuiteClient $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = new SerializedSuiteClient(
            new SerializedSuiteFactory(),
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
