<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration;

use SmartAssert\ApiClient\Model\RefreshableToken;

class CreateFrontendTokenTest extends AbstractIntegrationTestCase
{
    public function testCreateSuccess(): void
    {
        $refreshableToken = self::$client->createUserFrontendToken(self::USER1_EMAIL, self::USER1_PASSWORD);

        self::assertInstanceOf(RefreshableToken::class, $refreshableToken);
    }
}
