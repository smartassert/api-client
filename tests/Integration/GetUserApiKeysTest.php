<?php

declare(strict_types=1);

namespace Integration;

use SmartAssert\ApiClient\Exception\UnauthorizedException;
use SmartAssert\ApiClient\Model\ApiKey;
use SmartAssert\ApiClient\Tests\Integration\AbstractIntegrationTestCase;

class GetUserApiKeysTest extends AbstractIntegrationTestCase
{
    public function testGetUserApiKeysInvalidToken(): void
    {
        self::expectException(UnauthorizedException::class);

        self::$client->getUserApiKeys(md5((string) rand()));
    }

    public function testGetUserApiKeysSuccess(): void
    {
        $refreshableToken = self::$client->createUserToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKeys = self::$client->getUserApiKeys($refreshableToken->token);

        self::assertInstanceOf(ApiKey::class, $apiKeys[0]);
    }
}
