<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration;

use SmartAssert\ApiClient\Exception\UnauthorizedException;
use SmartAssert\ApiClient\Exception\UserAlreadyExistsException;

class CreateUserTest extends AbstractIntegrationTestCase
{
    public function testCreateUnauthorized(): void
    {
        self::expectException(UnauthorizedException::class);

        self::$client->createUser(md5((string) rand()), md5((string) rand()), md5((string) rand()));
    }

    public function testCreateUserAlreadyExists(): void
    {
        self::expectException(UserAlreadyExistsException::class);

        self::$client->createUser('primary_admin_token', self::USER1_EMAIL, md5((string) rand()));
    }

    public function testCreateUserSuccess(): void
    {
        $userIdentifier = md5((string) rand());
        $password = md5((string) rand());

        $user = self::$client->createUser('primary_admin_token', $userIdentifier, $password);

        self::assertSame($userIdentifier, $user->userIdentifier);
    }
}
