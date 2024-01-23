<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Functional\Client\FileSourceClient;

use GuzzleHttp\Psr7\HttpFactory;
use SmartAssert\ApiClient\Factory\Source\SourceFactory;
use SmartAssert\ApiClient\FileSourceClient;
use SmartAssert\ApiClient\RequestBuilder\RequestBuilder;
use SmartAssert\ApiClient\ServiceClient\HttpHandler;
use SmartAssert\ApiClient\Tests\Functional\Client\AbstractClientTestCase;
use SmartAssert\ApiClient\Tests\Functional\DataProvider\CommonNonSuccessResponseDataProviderTrait;
use SmartAssert\ApiClient\UrlGeneratorFactory;

abstract class AbstractFileSourceClientTestCase extends AbstractClientTestCase
{
    use CommonNonSuccessResponseDataProviderTrait;

    protected const ID = 'id';
    protected const LABEL = 'label';

    protected FileSourceClient $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = new FileSourceClient(
            new SourceFactory(),
            new HttpHandler($this->httpClient, $this->exceptionFactory),
            new RequestBuilder(
                new HttpFactory(),
                UrlGeneratorFactory::create('https://api.example.com'),
            )
        );
    }

    protected function getExpectedAuthorizationHeader(): string
    {
        return 'Bearer ' . self::API_KEY;
    }
}
