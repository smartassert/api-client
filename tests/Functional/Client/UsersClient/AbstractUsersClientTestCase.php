<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Functional\Client\UsersClient;

use GuzzleHttp\Psr7\HttpFactory;
use SmartAssert\ApiClient\Tests\Functional\Client\AbstractClientTestCase;
use SmartAssert\ApiClient\Tests\Functional\DataProvider\CommonNonSuccessResponseDataProviderTrait;
use SmartAssert\ApiClient\UrlGeneratorFactory;
use SmartAssert\ApiClient\UsersClient;
use SmartAssert\ServiceClient\Client as ServiceClient;
use SmartAssert\ServiceClient\ExceptionFactory\CurlExceptionFactory;
use SmartAssert\ServiceClient\ResponseFactory\ResponseFactory;

abstract class AbstractUsersClientTestCase extends AbstractClientTestCase
{
    use CommonNonSuccessResponseDataProviderTrait;

    protected const ID = 'id';
    protected const IDENTIFIER = 'identifier';

    protected UsersClient $client;

    protected function setUp(): void
    {
        parent::setUp();

        $httpFactory = new HttpFactory();

        $this->client = new UsersClient(
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
}
