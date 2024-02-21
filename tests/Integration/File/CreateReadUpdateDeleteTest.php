<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\File;

use SmartAssert\ApiClient\Exception\ClientException;
use SmartAssert\ApiClient\Exception\Error\ErrorException;
use SmartAssert\ApiClient\Exception\File\NotFoundException as FileNotFoundException;
use SmartAssert\ApiClient\Exception\UnauthorizedException;
use SmartAssert\ServiceRequest\Error\BadRequestError;
use SmartAssert\ServiceRequest\Error\BadRequestErrorInterface;
use SmartAssert\ServiceRequest\Error\DuplicateObjectError;
use SmartAssert\ServiceRequest\Error\DuplicateObjectErrorInterface;
use SmartAssert\ServiceRequest\Parameter\Parameter;
use SmartAssert\ServiceRequest\Parameter\Requirements;

class CreateReadUpdateDeleteTest extends AbstractFileTestCase
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

    public function testReadUnauthorized(): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);
        $source = self::$fileSourceClient->create($apiKey->key, md5((string) rand()));

        $filename = md5((string) rand()) . '.yaml';
        self::$fileClient->create($apiKey->key, $source->getId(), $filename, md5((string) rand()));

        $exception = null;

        try {
            self::$fileClient->read(md5((string) rand()), $source->getId(), $filename);
        } catch (ClientException $exception) {
        }

        self::assertInstanceOf(ClientException::class, $exception);

        $fileNotFoundException = $exception->getInnerException();
        self::assertInstanceOf(FileNotFoundException::class, $fileNotFoundException);
    }

    public function testReadEmptyFilename(): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);
        $source = self::$fileSourceClient->create($apiKey->key, md5((string) rand()));

        $exception = null;

        try {
            self::$fileClient->read($apiKey->key, $source->getId(), '');
        } catch (ClientException $exception) {
        }

        self::assertInstanceOf(ClientException::class, $exception);
        self::assertInstanceOf(FileNotFoundException::class, $exception->getInnerException());
    }

    public function testUpdateUnauthorized(): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);
        $source = self::$fileSourceClient->create($apiKey->key, md5((string) rand()));

        $filename = md5((string) rand()) . '.yaml';
        self::$fileClient->create($apiKey->key, $source->getId(), $filename, md5((string) rand()));

        $exception = null;

        try {
            self::$fileClient->update(md5((string) rand()), $source->getId(), $filename, md5((string) rand()));
        } catch (ClientException $exception) {
        }

        self::assertInstanceOf(ClientException::class, $exception);

        $fileNotFoundException = $exception->getInnerException();
        self::assertInstanceOf(FileNotFoundException::class, $fileNotFoundException);
    }

    public function testUpdateEmptyFilename(): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);
        $source = self::$fileSourceClient->create($apiKey->key, md5((string) rand()));

        $exception = null;

        try {
            self::$fileClient->update($apiKey->key, $source->getId(), '', md5((string) rand()));
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

    public function testDeleteUnauthorized(): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);
        $source = self::$fileSourceClient->create($apiKey->key, md5((string) rand()));

        $filename = md5((string) rand()) . '.yaml';
        self::$fileClient->create($apiKey->key, $source->getId(), $filename, md5((string) rand()));

        $exception = null;

        try {
            self::$fileClient->delete(md5((string) rand()), md5((string) rand()), md5((string) rand()) . '.yaml');
        } catch (ClientException $exception) {
        }

        self::assertInstanceOf(ClientException::class, $exception);

        $fileNotFoundException = $exception->getInnerException();
        self::assertInstanceOf(FileNotFoundException::class, $fileNotFoundException);
    }

    public function testDeleteEmptyFilename(): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);
        $source = self::$fileSourceClient->create($apiKey->key, md5((string) rand()));

        self::expectNotToPerformAssertions();
        self::$fileClient->delete($apiKey->key, $source->getId(), '');
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

    public function testCreateReadUpdateDeleteSuccess(): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        $label = md5((string) rand());

        $fileSource = self::$fileSourceClient->create($apiKey->key, $label);

        $filename = md5((string) rand()) . '.yaml';
        $content = md5((string) rand());

        self::$fileClient->create($apiKey->key, $fileSource->id, $filename, $content);

        $readContent = self::$fileClient->read($apiKey->key, $fileSource->id, $filename);
        self::assertSame($content, $readContent);

        $updatedContent = md5((string) rand());
        self::assertNotSame($content, $updatedContent);

        self::$fileClient->update($apiKey->key, $fileSource->id, $filename, $updatedContent);

        $readUpdatedContent = self::$fileClient->read($apiKey->key, $fileSource->id, $filename);
        self::assertSame($updatedContent, $readUpdatedContent);

        self::$fileClient->delete($apiKey->key, $fileSource->id, $filename);

        $exception = null;

        try {
            self::$fileClient->read($apiKey->key, $fileSource->id, $filename);
        } catch (ClientException $exception) {
        }

        self::assertInstanceOf(ClientException::class, $exception);

        $fileNotFoundException = $exception->getInnerException();
        self::assertInstanceOf(FileNotFoundException::class, $fileNotFoundException);
    }
}
