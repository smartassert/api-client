<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Functional\Client;

use GuzzleHttp\Psr7\Response;
use SmartAssert\ApiClient\Model\RefreshableToken;
use SmartAssert\ApiClient\Tests\Functional\DataProvider\InvalidJsonResponseExceptionDataProviderTrait;
use SmartAssert\ApiClient\Tests\Functional\DataProvider\NetworkErrorExceptionDataProviderTrait;

class CreateUserTokenTest extends AbstractClientModelCreationTestCase
{
    use InvalidJsonResponseExceptionDataProviderTrait;
    use NetworkErrorExceptionDataProviderTrait;

    public function testCreateUserTokenRequestProperties(): void
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

        $userIdentifier = md5((string) rand());
        $password = md5((string) rand());

        $this->client->createUserToken($userIdentifier, $password);

        $request = $this->getLastRequest();
        self::assertSame('POST', $request->getMethod());
        self::assertEmpty($request->getHeaderLine('authorization'));
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
            $this->client->createUserToken('user identifier', 'password');
        };
    }

    protected function getExpectedModelClass(): string
    {
        return RefreshableToken::class;
    }
}
