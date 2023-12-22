<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\User;

use SmartAssert\ApiClient\Exception\Http\UnauthorizedException;
use SmartAssert\ApiClient\Exception\User\AlreadyExistsException;
use SmartAssert\ApiClient\Tests\Integration\AbstractIntegrationTestCase;

class CreateTest extends AbstractIntegrationTestCase
{
    public function testCreateUnauthorized(): void
    {
        self::expectException(UnauthorizedException::class);

        self::$usersClient->create(md5((string) rand()), md5((string) rand()), md5((string) rand()));
    }

    public function testCreateUserAlreadyExists(): void
    {
        self::expectException(AlreadyExistsException::class);

        self::$usersClient->create('primary_admin_token', self::USER1_EMAIL, md5((string) rand()));
    }

    public function testCreateUserSuccess(): void
    {
        $userIdentifier = md5((string) rand());
        $password = md5((string) rand());

        $user = self::$usersClient->create('primary_admin_token', $userIdentifier, $password);

        self::assertSame($userIdentifier, $user->userIdentifier);
    }
}
