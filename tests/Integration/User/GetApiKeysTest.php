<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\User;

use SmartAssert\ApiClient\Model\ApiKey;
use SmartAssert\ApiClient\Tests\Integration\AbstractIntegrationTestCase;
use SmartAssert\ServiceClient\Exception\UnauthorizedException;

class GetApiKeysTest extends AbstractIntegrationTestCase
{
    public function testGetUserApiKeysInvalidToken(): void
    {
        self::expectException(UnauthorizedException::class);

        self::$usersClient->getApiKeys(md5((string) rand()));
    }

    public function testGetUserApiKeysSuccess(): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKeys = self::$usersClient->getApiKeys($refreshableToken->token);

        self::assertInstanceOf(ApiKey::class, $apiKeys[0]);
    }
}
