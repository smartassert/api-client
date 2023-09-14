<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration;

class VerifyFrontendTokenTest extends AbstractIntegrationTestCase
{
    public function testVerifySuccess(): void
    {
        $refreshableToken = self::$client->createUserFrontendToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $user = self::$client->verifyUserFrontendToken($refreshableToken->token);

        self::assertSame(self::USER1_EMAIL, $user->userIdentifier);
    }
}
