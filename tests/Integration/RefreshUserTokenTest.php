<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration;

use SmartAssert\ApiClient\Exception\UnauthorizedException;
use SmartAssert\ApiClient\Model\RefreshableToken;

class RefreshUserTokenTest extends AbstractIntegrationTestCase
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
