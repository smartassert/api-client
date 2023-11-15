<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\User;

use SmartAssert\ApiClient\Model\ApiKey;
use SmartAssert\ApiClient\Tests\Integration\AbstractIntegrationTestCase;
use SmartAssert\ServiceClient\Exception\UnauthorizedException;

class GetApiKeyTest extends AbstractIntegrationTestCase
{
    public function testGetUserApiKeyInvalidToken(): void
    {
        self::expectException(UnauthorizedException::class);

        self::$usersClient->getApiKey(md5((string) rand()));
    }

    public function testGetUserApiKeySuccess(): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        self::assertInstanceOf(ApiKey::class, $apiKey);
    }
}
