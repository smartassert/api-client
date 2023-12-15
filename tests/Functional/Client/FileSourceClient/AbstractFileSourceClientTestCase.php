<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Functional\Client\FileSourceClient;

use GuzzleHttp\Psr7\HttpFactory;
use SmartAssert\ApiClient\Factory\Source\SourceFactory;
use SmartAssert\ApiClient\FileSourceClient;
use SmartAssert\ApiClient\Tests\Functional\Client\AbstractClientTestCase;
use SmartAssert\ApiClient\Tests\Functional\DataProvider\CommonNonSuccessResponseDataProviderTrait;
use SmartAssert\ApiClient\UrlGeneratorFactory;
use SmartAssert\ServiceClient\Client as ServiceClient;
use SmartAssert\ServiceClient\ExceptionFactory\CurlExceptionFactory;
use SmartAssert\ServiceClient\ResponseFactory\ResponseFactory;

abstract class AbstractFileSourceClientTestCase extends AbstractClientTestCase
{
    use CommonNonSuccessResponseDataProviderTrait;

    protected const ID = 'id';
    protected const LABEL = 'label';

    protected FileSourceClient $client;

    protected function setUp(): void
    {
        parent::setUp();

        $httpFactory = new HttpFactory();

        $this->client = new FileSourceClient(
            UrlGeneratorFactory::create('https://api.example.com'),
            new ServiceClient(
                $httpFactory,
                $httpFactory,
                $this->httpClient,
                ResponseFactory::createFactory(),
                new CurlExceptionFactory(),
            ),
            new SourceFactory(),
        );
    }

    protected function getExpectedAuthorizationHeader(): string
    {
        return 'Bearer ' . self::API_KEY;
    }
}
