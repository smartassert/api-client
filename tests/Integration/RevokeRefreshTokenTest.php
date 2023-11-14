<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration;

use SmartAssert\ApiClient\Exception\UnauthorizedException;

class RevokeRefreshTokenTest extends AbstractIntegrationTestCase
{
    public function testRefreshSuccess(): void
    {
        $refreshableToken = self::$client->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $user = self::$client->verifyToken($refreshableToken->token);

        $refreshedToken = self::$client->refreshToken($refreshableToken->refreshToken);

        self::$client->revokeRefreshToken($refreshableToken->token, $refreshableToken->refreshToken);

        self::expectException(UnauthorizedException::class);
        self::$client->refreshToken($refreshedToken->refreshToken);
    }
}