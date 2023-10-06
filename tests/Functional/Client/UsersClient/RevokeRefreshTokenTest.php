<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Functional\Client\UsersClient;

use GuzzleHttp\Psr7\Response;
use SmartAssert\ApiClient\Tests\Functional\DataProvider\NetworkErrorExceptionDataProviderTrait;

class RevokeRefreshTokenTest extends AbstractUsersClientTestCase
{
    use NetworkErrorExceptionDataProviderTrait;

    public function testRevokeRefreshTokenRequestProperties(): void
    {
        $this->mockHandler->append(new Response(
            200,
            ['content-type' => 'application/json'],
            ''
        ));

        $token = md5((string) rand());
        $refreshToken = md5((string) rand());

        $this->client->revokeRefreshToken($token, $refreshToken);

        $request = $this->getLastRequest();
        self::assertSame('POST', $request->getMethod());
        self::assertSame('Bearer ' . $token, $request->getHeaderLine('authorization'));
    }

    public static function clientActionThrowsExceptionDataProvider(): array
    {
        return self::networkErrorExceptionDataProvider();
    }

    protected function createClientActionCallable(): callable
    {
        return function () {
            $this->client->revokeRefreshToken('token', 'refresh token');
        };
    }
}
