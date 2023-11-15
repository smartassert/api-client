<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\User;

use SmartAssert\ApiClient\Model\RefreshableToken;
use SmartAssert\ApiClient\Tests\Integration\AbstractIntegrationTestCase;
use SmartAssert\ServiceClient\Exception\UnauthorizedException;

class RefreshTokenTest extends AbstractIntegrationTestCase
{
    public function testRefreshInvalidRefreshToken(): void
    {
        self::expectException(UnauthorizedException::class);

        self::$client->refreshToken(md5((string) rand()));
    }

    public function testRefreshSuccess(): void
    {
        $refreshableToken = self::$client->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $refreshedToken = self::$client->refreshToken($refreshableToken->refreshToken);

        self::assertInstanceOf(RefreshableToken::class, $refreshedToken);
    }
}
