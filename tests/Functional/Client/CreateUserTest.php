<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Functional\Client;

use GuzzleHttp\Psr7\Response;
use SmartAssert\ApiClient\Model\User;
use SmartAssert\ApiClient\Tests\Functional\DataProvider\InvalidJsonResponseExceptionDataProviderTrait;
use SmartAssert\ApiClient\Tests\Functional\DataProvider\NetworkErrorExceptionDataProviderTrait;

class CreateUserTest extends AbstractClientModelCreationTestCase
{
    use InvalidJsonResponseExceptionDataProviderTrait;
    use NetworkErrorExceptionDataProviderTrait;

    public function testCreateUserRequestProperties(): void
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

        $adminToken = md5((string) rand());
        $password = md5((string) rand());

        $this->client->createUser($adminToken, $userIdentifier, $password);

        $request = $this->getLastRequest();
        self::assertSame('POST', $request->getMethod());
        self::assertSame('Bearer ' . $adminToken, $request->getHeaderLine('authorization'));
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
            $this->client->createUser('admin token', 'user identifier', 'password');
        };
    }

    protected function getExpectedModelClass(): string
    {
        return User::class;
    }
}
