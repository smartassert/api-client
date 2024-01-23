<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Functional\Client\FileClient;

use GuzzleHttp\Psr7\HttpFactory;
use SmartAssert\ApiClient\FileClient;
use SmartAssert\ApiClient\RequestBuilder\RequestBuilder;
use SmartAssert\ApiClient\ServiceClient\HttpHandler;
use SmartAssert\ApiClient\Tests\Functional\Client\AbstractClientTestCase;
use SmartAssert\ApiClient\Tests\Functional\DataProvider\CommonNonSuccessResponseDataProviderTrait;
use SmartAssert\ApiClient\UrlGeneratorFactory;

abstract class AbstractFileClientTestCase extends AbstractClientTestCase
{
    use CommonNonSuccessResponseDataProviderTrait;

    protected const ID = 'id';
    protected const LABEL = 'label';

    protected FileClient $client;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = new FileClient(
            UrlGeneratorFactory::create('https://api.example.com'),
            new HttpHandler($this->httpClient, $this->exceptionFactory),
            new RequestBuilder(
                new HttpFactory(),
            )
        );
    }

    protected function getExpectedAuthorizationHeader(): string
    {
        return 'Bearer ' . self::API_KEY;
    }
}
