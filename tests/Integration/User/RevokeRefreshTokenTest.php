<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\User;

use SmartAssert\ApiClient\Exception\UnauthorizedException;
use SmartAssert\ApiClient\Tests\Integration\AbstractIntegrationTestCase;

class RevokeRefreshTokenTest extends AbstractIntegrationTestCase
{
    public function testRefreshSuccess(): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        self::$usersClient->verifyToken($refreshableToken->token);

        $refreshedToken = self::$usersClient->refreshToken($refreshableToken->refreshToken);
        self::$usersClient->revokeRefreshToken($refreshableToken->token, $refreshableToken->refreshToken);

        self::expectException(UnauthorizedException::class);
        self::$usersClient->refreshToken($refreshedToken->refreshToken);
    }
}
