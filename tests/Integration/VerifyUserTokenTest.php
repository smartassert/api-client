<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration;

class VerifyUserTokenTest extends AbstractIntegrationTestCase
{
    public function testVerifySuccess(): void
    {
        $refreshableToken = self::$client->createUserToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $user = self::$client->verifyUserToken($refreshableToken->token);

        self::assertSame(self::USER1_EMAIL, $user->userIdentifier);
    }
}
