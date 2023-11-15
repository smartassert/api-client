<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\User;

use SmartAssert\ApiClient\Tests\Integration\AbstractIntegrationTestCase;

class VerifyTokenTest extends AbstractIntegrationTestCase
{
    public function testVerifySuccess(): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $user = self::$usersClient->verifyToken($refreshableToken->token);

        self::assertSame(self::USER1_EMAIL, $user->userIdentifier);
    }
}
