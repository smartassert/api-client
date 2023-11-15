<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\User;

use SmartAssert\ServiceClient\Exception\UnauthorizedException;

class RevokeAllRefreshTokensForUserTest extends AbstractUserTestCase
{
    public function testRefreshSuccess(): void
    {
        $refreshableToken = self::$client->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $user = self::$client->verifyToken($refreshableToken->token);

        $refreshedToken = self::$client->refreshToken($refreshableToken->refreshToken);

        self::$client->revokeAllRefreshTokensForUser('primary_admin_token', $user->id);

        self::expectException(UnauthorizedException::class);
        self::$client->refreshToken($refreshedToken->refreshToken);
    }
}
