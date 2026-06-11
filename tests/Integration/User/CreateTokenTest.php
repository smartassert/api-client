<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\User;

use SmartAssert\ApiClient\Exception\ClientExceptionInterface;
use SmartAssert\ApiClient\Exception\UnauthorizedException;
use SmartAssert\ApiClient\Tests\Integration\AbstractIntegrationTestCase;

class CreateTokenTest extends AbstractIntegrationTestCase
{
    public function testCreateUnauthorized(): void
    {
        $exception = null;

        try {
            self::$usersClient->createToken(self::USER1_EMAIL, md5((string) rand()));
        } catch (ClientExceptionInterface $exception) {
        }

        self::assertInstanceOf(UnauthorizedException::class, $exception);
    }

    public function testCreateSuccess(): void
    {
        self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);

        self::expectNotToPerformAssertions();
    }
}
