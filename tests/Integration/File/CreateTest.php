<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\File;

use SmartAssert\ApiClient\Exception\ClientException;
use SmartAssert\ApiClient\Exception\Error\ErrorException;
use SmartAssert\ApiClient\Exception\ForbiddenException;
use SmartAssert\ApiClient\Exception\UnauthorizedException;
use SmartAssert\ServiceRequest\Error\BadRequestError;
use SmartAssert\ServiceRequest\Error\BadRequestErrorInterface;
use SmartAssert\ServiceRequest\Error\DuplicateObjectError;
use SmartAssert\ServiceRequest\Error\DuplicateObjectErrorInterface;
use SmartAssert\ServiceRequest\Parameter\Parameter;
use SmartAssert\ServiceRequest\Parameter\Requirements;
use Symfony\Component\Uid\Ulid;

class CreateTest extends AbstractFileTestCase
{
    public function testCreateUnauthorized(): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);
        $source = self::$fileSourceClient->create($apiKey->key, md5((string) rand()));

        $exception = null;

        try {
            self::$fileClient->create(
                md5((string) rand()),
                $source->getId(),
                md5((string) rand()) . '.yaml',
                md5((string) rand())
            );
        } catch (ClientException $exception) {
        }

        self::assertInstanceOf(ClientException::class, $exception);
        self::assertInstanceOf(UnauthorizedException::class, $exception->getInnerException());
    }

    public function testCreateEmptyFilename(): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        $label = md5((string) rand());

        $fileSource = self::$fileSourceClient->create($apiKey->key, $label);

        $exception = null;

        try {
            self::$fileClient->create($apiKey->key, $fileSource->id, '', md5((string) rand()));
        } catch (ClientException $exception) {
        }

        self::assertInstanceOf(ClientException::class, $exception);

        $errorException = $exception->getInnerException();
        self::assertInstanceOf(ErrorException::class, $errorException);

        $error = $errorException->getError();
        self::assertInstanceOf(BadRequestErrorInterface::class, $error);

        self::assertEquals(
            new BadRequestError(
                (new Parameter('filename', ''))
                    ->withRequirements(new Requirements('yaml_filename')),
                'invalid'
            ),
            $error
        );
    }

    public function testCreateDuplicateFilename(): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        $label = md5((string) rand());

        $fileSource = self::$fileSourceClient->create($apiKey->key, $label);

        $filename = md5((string) rand()) . '.yaml';
        $content = md5((string) rand());

        self::$fileClient->create($apiKey->key, $fileSource->id, $filename, $content);

        $exception = null;

        try {
            self::$fileClient->create($apiKey->key, $fileSource->id, $filename, $content);
        } catch (ClientException $exception) {
        }

        self::assertInstanceOf(ClientException::class, $exception);

        $errorException = $exception->getInnerException();
        self::assertInstanceOf(ErrorException::class, $errorException);

        $error = $errorException->getError();
        self::assertInstanceOf(DuplicateObjectErrorInterface::class, $error);
        self::assertEquals(new DuplicateObjectError(new Parameter('filename', $filename)), $error);
    }

    public function testCreateSourceForbidden(): void
    {
        $user1RefreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $user1ApiKey = self::$usersClient->getApiKey($user1RefreshableToken->token);

        $user2RefreshableToken = self::$usersClient->createToken(self::USER2_EMAIL, self::USER2_PASSWORD);
        $user2ApiKey = self::$usersClient->getApiKey($user2RefreshableToken->token);

        $source = self::$fileSourceClient->create($user2ApiKey->key, md5((string) rand()));

        $exception = null;

        try {
            self::$fileClient->create(
                $user1ApiKey->key,
                $source->id,
                md5((string) rand()) . '.yaml',
                md5((string) rand())
            );
        } catch (ClientException $exception) {
        }

        self::assertInstanceOf(ClientException::class, $exception);
        self::assertInstanceOf(ForbiddenException::class, $exception->getInnerException());
    }

    public function testCreateSourceDoesNotExist(): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        $sourceId = (string) new Ulid();
        \assert('' !== $sourceId);

        $exception = null;

        try {
            self::$fileClient->create($apiKey->key, $sourceId, md5((string) rand()) . '.yaml', md5((string) rand()));
        } catch (ClientException $exception) {
        }

        self::assertInstanceOf(ClientException::class, $exception);
        self::assertInstanceOf(ForbiddenException::class, $exception->getInnerException());
    }
}
