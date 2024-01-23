<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Functional\Client\UsersClient;

use GuzzleHttp\Psr7\HttpFactory;
use SmartAssert\ApiClient\Factory\User\ApiKeyFactory;
use SmartAssert\ApiClient\Factory\User\TokenFactory;
use SmartAssert\ApiClient\Factory\User\UserFactory;
use SmartAssert\ApiClient\ServiceClient\HttpHandler;
use SmartAssert\ApiClient\Tests\Functional\Client\AbstractClientTestCase;
use SmartAssert\ApiClient\Tests\Functional\DataProvider\CommonNonSuccessResponseDataProviderTrait;
use SmartAssert\ApiClient\UrlGeneratorFactory;
use SmartAssert\ApiClient\UsersClient;

abstract class AbstractUsersClientTestCase extends AbstractClientTestCase
{
    use CommonNonSuccessResponseDataProviderTrait;

    protected const ID = 'id';
    protected const IDENTIFIER = 'identifier';

    protected UsersClient $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = new UsersClient(
            new HttpHandler(
                $this->httpClient,
                $this->exceptionFactory,
                new HttpFactory(),
                UrlGeneratorFactory::create('https://api.example.com'),
            ),
            new TokenFactory(),
            new UserFactory(),
            new ApiKeyFactory(),
        );
    }
}
