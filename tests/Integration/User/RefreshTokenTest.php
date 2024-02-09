<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\User;

use SmartAssert\ApiClient\Data\User\Token;
use SmartAssert\ApiClient\Exception\ClientException;
use SmartAssert\ApiClient\Exception\UnauthorizedException;
use SmartAssert\ApiClient\Tests\Integration\AbstractIntegrationTestCase;

class RefreshTokenTest extends AbstractIntegrationTestCase
{
    public function testRefreshInvalidRefreshToken(): void
    {
        $exception = null;

        try {
            self::$usersClient->refreshToken(md5((string) rand()));
        } catch (ClientException $exception) {
        }

        self::assertInstanceOf(ClientException::class, $exception);
        self::assertInstanceOf(UnauthorizedException::class, $exception->getInnerException());
    }

    public function testRefreshSuccess(): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $refreshedToken = self::$usersClient->refreshToken($refreshableToken->refreshToken);

        self::assertInstanceOf(Token::class, $refreshedToken);
    }
}
