<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\User;

use SmartAssert\ApiClient\Tests\Integration\AbstractIntegrationTestCase;
use SmartAssert\ServiceClient\Exception\UnauthorizedException;

class RevokeAllRefreshTokensForUserTest extends AbstractIntegrationTestCase
{
    public function testRefreshSuccess(): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $user = self::$usersClient->verifyToken($refreshableToken->token);

        $refreshedToken = self::$usersClient->refreshToken($refreshableToken->refreshToken);

        self::$usersClient->revokeAllRefreshTokensForUser('primary_admin_token', $user->id);

        self::expectException(UnauthorizedException::class);
        self::$usersClient->refreshToken($refreshedToken->refreshToken);
    }
}
