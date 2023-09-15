<?php

declare(strict_types=1);

namespace Functional\Client;

use GuzzleHttp\Psr7\Response;
use SmartAssert\ApiClient\Tests\Functional\Client\AbstractClientTestCase;
use SmartAssert\ApiClient\Tests\Functional\DataProvider\NetworkErrorExceptionDataProviderTrait;

class RevokeRefreshTokenTest extends AbstractClientTestCase
{
    use NetworkErrorExceptionDataProviderTrait;

    public function testRefreshUserTokenRequestProperties(): void
    {
        $this->mockHandler->append(new Response(
            200,
            ['content-type' => 'application/json'],
            ''
        ));

        $adminToken = md5((string) rand());
        $userId = md5((string) rand());

        $this->client->revokeRefreshToken($adminToken, $userId);

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
            $this->client->revokeRefreshToken('admin token', 'user id');
        };
    }
}
