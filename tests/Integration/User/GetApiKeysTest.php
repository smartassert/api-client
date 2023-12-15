<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\User;

use SmartAssert\ApiClient\Data\User\ApiKey;
use SmartAssert\ApiClient\FooException\Http\UnauthorizedException;
use SmartAssert\ApiClient\Tests\Integration\AbstractIntegrationTestCase;

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
