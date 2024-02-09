<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\User;

use SmartAssert\ApiClient\Exception\ClientException;
use SmartAssert\ApiClient\Exception\Error\ErrorException;
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
        $exception = null;

        try {
            self::$usersClient->create(md5((string) rand()), md5((string) rand()), md5((string) rand()));
        } catch (ClientException $exception) {
        }

        self::assertInstanceOf(ClientException::class, $exception);
        self::assertInstanceOf(UnauthorizedException::class, $exception->getInnerException());
    }

    public function testCreateBadRequest(): void
    {
        $userIdentifier = str_repeat('.', 255);

        $exception = null;

        try {
            self::$usersClient->create('primary_admin_token', $userIdentifier, md5((string) rand()));
        } catch (ClientException $exception) {
        }

        self::assertInstanceOf(ClientException::class, $exception);

        $errorException = $exception->getInnerException();
        self::assertInstanceOf(ErrorException::class, $errorException);

        $error = $errorException->getError();
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
        $exception = null;

        try {
            self::$usersClient->create('primary_admin_token', self::USER1_EMAIL, md5((string) rand()));
        } catch (ClientException $exception) {
        }

        self::assertInstanceOf(ClientException::class, $exception);
        self::assertInstanceOf(AlreadyExistsException::class, $exception->getInnerException());
    }

    public function testCreateUserSuccess(): void
    {
        $userIdentifier = md5((string) rand());
        $password = md5((string) rand());

        $user = self::$usersClient->create('primary_admin_token', $userIdentifier, $password);

        self::assertSame($userIdentifier, $user->userIdentifier);
    }
}
