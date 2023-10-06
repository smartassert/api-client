<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Functional\Client\UsersClient;

use GuzzleHttp\Psr7\Response;
use SmartAssert\ApiClient\Tests\Functional\DataProvider\NetworkErrorExceptionDataProviderTrait;

class RevokeAllRefreshTokensForUserTest extends AbstractUsersClientTestCase
{
    use NetworkErrorExceptionDataProviderTrait;

    public function testRevokeRefreshTokenRequestProperties(): void
    {
        $this->mockHandler->append(new Response(
            200,
            ['content-type' => 'application/json'],
            ''
        ));

        $adminToken = md5((string) rand());
        $userId = md5((string) rand());

        $this->client->revokeAllRefreshTokensForUser($adminToken, $userId);

        $request = $this->getLastRequest();
        self::assertSame('POST', $request->getMethod());
        self::assertSame('Bearer ' . $adminToken, $request->getHeaderLine('authorization'));
    }

    public static function clientActionThrowsExceptionDataProvider(): array
    {
        return self::networkErrorExceptionDataProvider();
    }

    protected function createClientActionCallable(): callable
    {
        return function () {
            $this->client->revokeAllRefreshTokensForUser('admin token', 'user id');
        };
    }
}
