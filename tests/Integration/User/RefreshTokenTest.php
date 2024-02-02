<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\User;

use SmartAssert\ApiClient\Data\User\Token;
use SmartAssert\ApiClient\Exception\UnauthorizedException;
use SmartAssert\ApiClient\Tests\Integration\AbstractIntegrationTestCase;

class RefreshTokenTest extends AbstractIntegrationTestCase
{
    public function testRefreshInvalidRefreshToken(): void
    {
        self::expectException(UnauthorizedException::class);

        self::$usersClient->refreshToken(md5((string) rand()));
    }

    public function testRefreshSuccess(): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $refreshedToken = self::$usersClient->refreshToken($refreshableToken->refreshToken);

        self::assertInstanceOf(Token::class, $refreshedToken);
    }
}
