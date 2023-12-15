<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Functional\Client\UsersClient;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\ApiClient\Tests\Functional\Client\ExpectedRequestProperties;
use SmartAssert\ApiClient\Tests\Functional\Client\RequestAuthenticationTestTrait;
use SmartAssert\ApiClient\Tests\Functional\Client\RequestPropertiesTestTrait;
use SmartAssert\ApiClient\Tests\Functional\DataProvider\FooNetworkErrorExceptionDataProviderTrait;

class RevokeRefreshTokenTest extends AbstractUsersClientTestCase
{
    use FooNetworkErrorExceptionDataProviderTrait;
    use RequestPropertiesTestTrait;
    use RequestAuthenticationTestTrait;

    public static function clientActionThrowsExceptionDataProvider(): array
    {
        return self::networkErrorExceptionDataProvider();
    }

    protected function createClientActionCallable(): callable
    {
        return function () {
            $this->client->revokeRefreshToken('frontend token', 'refresh token');
        };
    }

    protected function getExpectedAuthorizationHeader(): string
    {
        return 'Bearer frontend token';
    }

    protected function getResponseFixture(): ResponseInterface
    {
        return new Response(200);
    }

    protected function getExpectedRequestProperties(): ExpectedRequestProperties
    {
        return new ExpectedRequestProperties('POST', '/user/refresh-token/revoke');
    }
}
