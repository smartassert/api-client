<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration;

use SmartAssert\ApiClient\Model\RefreshableToken;
use SmartAssert\ServiceClient\Exception\UnauthorizedException;

class CreateUserTokenTest extends AbstractIntegrationTestCase
{
    public function testCreateUnauthorized(): void
    {
        self::expectException(UnauthorizedException::class);

        self::$client->createToken(self::USER1_EMAIL, md5((string) rand()));
    }

    public function testCreateSuccess(): void
    {
        $refreshableToken = self::$client->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);

        self::assertInstanceOf(RefreshableToken::class, $refreshableToken);
    }
}
