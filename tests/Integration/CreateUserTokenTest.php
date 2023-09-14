<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration;

use SmartAssert\ApiClient\Model\RefreshableToken;

class CreateUserTokenTest extends AbstractIntegrationTestCase
{
    public function testCreateSuccess(): void
    {
        $refreshableToken = self::$client->createUserToken(self::USER1_EMAIL, self::USER1_PASSWORD);

        self::assertInstanceOf(RefreshableToken::class, $refreshableToken);
    }
}
