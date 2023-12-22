<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Functional\Client\UsersClient;

use GuzzleHttp\Psr7\HttpFactory;
use SmartAssert\ApiClient\ServiceClient\HttpHandler;
use SmartAssert\ApiClient\ServiceClient\RequestBuilder;
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
            UrlGeneratorFactory::create('https://api.example.com'),
            new HttpHandler($this->httpClient),
            new RequestBuilder(new HttpFactory()),
        );
    }
}
