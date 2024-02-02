<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\User;

use SmartAssert\ApiClient\Exception\ErrorExceptionInterface;
use SmartAssert\ApiClient\Exception\UnauthorizedException;
use SmartAssert\ApiClient\Exception\User\AlreadyExistsException;
use SmartAssert\ApiClient\Tests\Integration\AbstractIntegrationTestCase;
use SmartAssert\ServiceRequest\Error\BadRequestError;
use SmartAssert\ServiceRequest\Error\BadRequestErrorInterface;
use SmartAssert\ServiceRequest\Parameter\Parameter;
use SmartAssert\ServiceRequest\Parameter\Requirements;
use SmartAssert\ServiceRequest\Parameter\Size;

class CreateTest extends AbstractIntegrationTestCase
{
    public function testCreateUnauthorized(): void
    {
        self::expectException(UnauthorizedException::class);

        self::$usersClient->create(md5((string) rand()), md5((string) rand()), md5((string) rand()));
    }

    public function testCreateBadRequest(): void
    {
        $userIdentifier = str_repeat('.', 255);

        $exception = null;

        try {
            self::$usersClient->create('primary_admin_token', $userIdentifier, md5((string) rand()));
        } catch (ErrorExceptionInterface $exception) {
        }

        self::assertInstanceOf(ErrorExceptionInterface::class, $exception);

        $error = $exception->getError();
        self::assertInstanceOf(BadRequestErrorInterface::class, $error);
        self::assertEquals(
            new BadRequestError(
                (new Parameter('identifier', $userIdentifier))
                    ->withRequirements(new Requirements('string', new Size(1, 254))),
                'wrong_size'
            ),
            $error
        );
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
