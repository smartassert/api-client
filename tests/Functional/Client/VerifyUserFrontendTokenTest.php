<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Functional\Client;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use SmartAssert\ApiClient\Model\User;

class VerifyUserFrontendTokenTest extends AbstractClientModelCreationTestCase
{
    public function testVerifyUserFrontendTokenRequestProperties(): void
    {
        $id = md5((string) rand());
        $userIdentifier = md5((string) rand());

        $this->mockHandler->append(new Response(
            200,
            ['content-type' => 'application/json'],
            (string) json_encode([
                'user' => [
                    'id' => $id,
                    'user-identifier' => $userIdentifier,
                ],
            ])
        ));

        $token = md5((string) rand());

        $this->client->verifyUserFrontendToken($token);

        $request = $this->getLastRequest();
        self::assertSame('GET', $request->getMethod());
        self::assertSame('Bearer ' . $token, $request->getHeaderLine('authorization'));
    }

    /**
     * @dataProvider verifyFrontendTokenSuccessDataProvider
     */
    public function testVerifyUserFrontendTokenSuccess(ResponseInterface $httpFixture, user $expected): void
    {
        $this->mockHandler->append($httpFixture);

        $actual = $this->client->verifyUserFrontendToken('frontend token');
        self::assertEquals($expected, $actual);
    }

    /**
     * @return array<mixed>
     */
    public static function verifyFrontendTokenSuccessDataProvider(): array
    {
        $id = md5((string) rand());
        $userIdentifier = md5((string) rand());

        return [
            'created' => [
                'httpFixture' => new Response(
                    200,
                    [
                        'content-type' => 'application/json',
                    ],
                    (string) json_encode([
                        'user' => [
                            'id' => $id,
                            'user-identifier' => $userIdentifier,
                        ],
                    ])
                ),
                'expected' => new User($id, $userIdentifier),
            ],
        ];
    }

    protected function createClientActionCallable(): callable
    {
        return function () {
            $this->client->verifyUserFrontendToken('frontend token');
        };
    }

    protected function getExpectedModelClass(): string
    {
        return User::class;
    }
}
