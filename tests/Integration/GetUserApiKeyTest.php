<?php

declare(strict_types=1);

namespace Integration;

use SmartAssert\ApiClient\Exception\UnauthorizedException;
use SmartAssert\ApiClient\Model\ApiKey;
use SmartAssert\ApiClient\Tests\Integration\AbstractIntegrationTestCase;

class GetUserApiKeyTest extends AbstractIntegrationTestCase
{
    public function testGetUserApiKeyInvalidToken(): void
    {
        self::expectException(UnauthorizedException::class);

        self::$client->getUserApiKey(md5((string) rand()));
    }

    public function testGetUserApiKeySuccess(): void
    {
        $refreshableToken = self::$client->createUserToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$client->getUserApiKey($refreshableToken->token);

        self::assertInstanceOf(ApiKey::class, $apiKey);
    }
}
