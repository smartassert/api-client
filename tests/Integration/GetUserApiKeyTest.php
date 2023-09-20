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

        self::$client->getApiKey(md5((string) rand()));
    }

    public function testGetUserApiKeySuccess(): void
    {
        $refreshableToken = self::$client->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$client->getApiKey($refreshableToken->token);

        self::assertInstanceOf(ApiKey::class, $apiKey);
    }
}
