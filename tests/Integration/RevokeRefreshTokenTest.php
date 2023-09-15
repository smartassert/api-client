<?php

declare(strict_types=1);

namespace Integration;

use SmartAssert\ApiClient\Exception\UnauthorizedException;
use SmartAssert\ApiClient\Tests\Integration\AbstractIntegrationTestCase;

class RevokeRefreshTokenTest extends AbstractIntegrationTestCase
{
    public function testRefreshSuccess(): void
    {
        $refreshableToken = self::$client->createUserToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $user = self::$client->verifyUserToken($refreshableToken->token);

        $refreshedToken = self::$client->refreshUserToken($refreshableToken->refreshToken);

        self::$client->revokeRefreshToken('primary_admin_token', $user->id);

        self::expectException(UnauthorizedException::class);
        self::$client->refreshUserToken($refreshedToken->refreshToken);
    }
}
