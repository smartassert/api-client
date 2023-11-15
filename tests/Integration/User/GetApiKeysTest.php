<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\User;

use SmartAssert\ApiClient\Model\ApiKey;
use SmartAssert\ServiceClient\Exception\UnauthorizedException;

class GetApiKeysTest extends AbstractUserTestCase
{
    public function testGetUserApiKeysInvalidToken(): void
    {
        self::expectException(UnauthorizedException::class);

        self::$client->getApiKeys(md5((string) rand()));
    }

    public function testGetUserApiKeysSuccess(): void
    {
        $refreshableToken = self::$client->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKeys = self::$client->getApiKeys($refreshableToken->token);

        self::assertInstanceOf(ApiKey::class, $apiKeys[0]);
    }
}
