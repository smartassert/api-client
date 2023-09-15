<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Functional\Client;

use GuzzleHttp\Psr7\Response;
use SmartAssert\ApiClient\Model\RefreshableToken;
use SmartAssert\ApiClient\Tests\Functional\DataProvider\InvalidJsonResponseExceptionDataProviderTrait;
use SmartAssert\ApiClient\Tests\Functional\DataProvider\NetworkErrorExceptionDataProviderTrait;

class RefreshUserTokenTest extends AbstractClientModelCreationTestCase
{
    use InvalidJsonResponseExceptionDataProviderTrait;
    use NetworkErrorExceptionDataProviderTrait;

    public function testRefreshUserTokenRequestProperties(): void
    {
        $token = md5((string) rand());
        $refreshToken = md5((string) rand());

        $this->mockHandler->append(new Response(
            200,
            ['content-type' => 'application/json'],
            (string) json_encode([
                'refreshable_token' => [
                    'token' => $token,
                    'refresh_token' => $refreshToken,
                ],
            ])
        ));

        $this->client->refreshUserToken($refreshToken);

        $request = $this->getLastRequest();
        self::assertSame('POST', $request->getMethod());
        self::assertSame('Bearer ' . $refreshToken, $request->getHeaderLine('authorization'));
    }

    public static function clientActionThrowsExceptionDataProvider(): array
    {
        return array_merge(
            self::networkErrorExceptionDataProvider(),
            self::invalidJsonResponseExceptionDataProvider(),
        );
    }

    protected function createClientActionCallable(): callable
    {
        return function () {
            $this->client->refreshUserToken('refresh token');
        };
    }

    protected function getExpectedModelClass(): string
    {
        return RefreshableToken::class;
    }
}
