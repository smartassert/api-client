<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Functional\Client\FileClient;

use GuzzleHttp\Psr7\HttpFactory;
use SmartAssert\ApiClient\FileClient;
use SmartAssert\ApiClient\Tests\Functional\Client\AbstractClientTestCase;
use SmartAssert\ApiClient\Tests\Functional\DataProvider\CommonNonSuccessResponseDataProviderTrait;
use SmartAssert\ApiClient\UrlGeneratorFactory;
use SmartAssert\ServiceClient\Client as ServiceClient;
use SmartAssert\ServiceClient\ExceptionFactory\CurlExceptionFactory;
use SmartAssert\ServiceClient\ResponseFactory\ResponseFactory;

abstract class AbstractFileClientTestCase extends AbstractClientTestCase
{
    use CommonNonSuccessResponseDataProviderTrait;

    protected const ID = 'id';
    protected const LABEL = 'label';

    protected FileClient $client;

    protected function setUp(): void
    {
        parent::setUp();

        $httpFactory = new HttpFactory();

        $this->client = new FileClient(
            UrlGeneratorFactory::create('https://api.example.com'),
            new ServiceClient(
                $httpFactory,
                $httpFactory,
                $this->httpClient,
                ResponseFactory::createFactory(),
                new CurlExceptionFactory(),
            ),
        );
    }

    protected function getExpectedAuthorizationHeader(): string
    {
        return 'Bearer ' . self::API_KEY;
    }
}
