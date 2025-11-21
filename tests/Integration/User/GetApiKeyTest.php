<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\User;

use SmartAssert\ApiClient\Exception\ClientException;
use SmartAssert\ApiClient\Exception\UnauthorizedException;
use SmartAssert\ApiClient\Tests\Integration\AbstractIntegrationTestCase;

class GetApiKeyTest extends AbstractIntegrationTestCase
{
    public function testGetUserApiKeyInvalidToken(): void
    {
        $exception = null;

        try {
            self::$usersClient->getApiKey(md5((string) rand()));
        } catch (ClientException $exception) {
        }

        self::assertInstanceOf(ClientException::class, $exception);
        self::assertInstanceOf(UnauthorizedException::class, $exception->getInnerException());
    }

    public function testGetUserApiKeySuccess(): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        self::$usersClient->getApiKey($refreshableToken->token);

        self::expectNotToPerformAssertions();
    }
}
