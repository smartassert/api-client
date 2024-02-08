<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\User;

use SmartAssert\ApiClient\Exception\ClientException;
use SmartAssert\ApiClient\Exception\UnauthorizedException;
use SmartAssert\ApiClient\Tests\Integration\AbstractIntegrationTestCase;

class RevokeAllRefreshTokensForUserTest extends AbstractIntegrationTestCase
{
    public function testRefreshSuccess(): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $user = self::$usersClient->verifyToken($refreshableToken->token);

        $refreshedToken = self::$usersClient->refreshToken($refreshableToken->refreshToken);

        self::$usersClient->revokeAllRefreshTokensForUser('primary_admin_token', $user->id);

        $exception = null;

        try {
            self::$usersClient->refreshToken($refreshedToken->refreshToken);
        } catch (ClientException $exception) {
        }

        self::assertInstanceOf(ClientException::class, $exception);
        self::assertInstanceOf(UnauthorizedException::class, $exception->getInnerException());
    }
}
