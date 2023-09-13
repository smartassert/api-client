<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Functional\Client;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\ApiClient\Model\RefreshableToken;

class CreateUserFrontendTokenTest extends AbstractClientModelCreationTestCase
{
    public function testCreateUserFrontendTokenRequestProperties(): void
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

        $this->client->createUserFrontendToken($userIdentifier, $password);

        $request = $this->getLastRequest();
        self::assertSame('POST', $request->getMethod());
        self::assertEmpty($request->getHeaderLine('authorization'));
    }

    /**
     * @dataProvider createApiTokenSuccessDataProvider
     */
    public function testCreateUserFrontendTokenSuccess(ResponseInterface $httpFixture, RefreshableToken $expected): void
    {
        $this->mockHandler->append($httpFixture);

        $actual = $this->client->createUserFrontendToken('user identifier', 'password');
        self::assertEquals($expected, $actual);
    }

    /**
     * @return array<mixed>
     */
    public static function createApiTokenSuccessDataProvider(): array
    {
        $token = md5((string) rand());
        $refreshToken = md5((string) rand());

        return [
            'created' => [
                'httpFixture' => new Response(
                    200,
                    [
                        'content-type' => 'application/json',
                    ],
                    (string) json_encode([
                        'refreshable_token' => [
                            'token' => $token,
                            'refresh_token' => $refreshToken,
                        ],
                    ])
                ),
                'expected' => new RefreshableToken($token, $refreshToken),
            ],
        ];
    }

    protected function createClientActionCallable(): callable
    {
        return function () {
            $this->client->createUserFrontendToken('user identifier', 'password');
        };
    }

    protected function getExpectedModelClass(): string
    {
        return RefreshableToken::class;
    }
}
