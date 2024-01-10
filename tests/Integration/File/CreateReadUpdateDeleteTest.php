<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\File;

use SmartAssert\ApiClient\Exception\Error\DuplicateObjectException;
use SmartAssert\ApiClient\Exception\File\NotFoundException;
use SmartAssert\ApiClient\Exception\Http\UnauthorizedException;
use SmartAssert\ServiceRequest\Error\DuplicateObjectError;
use SmartAssert\ServiceRequest\Field\Field;

class CreateReadUpdateDeleteTest extends AbstractFileTestCase
{
    public function testCreateUnauthorized(): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);
        $source = self::$fileSourceClient->create($apiKey->key, md5((string) rand()));

        self::expectException(UnauthorizedException::class);
        self::$fileClient->create(
            md5((string) rand()),
            $source->getId(),
            md5((string) rand()) . '.yaml',
            md5((string) rand())
        );
    }

    public function testReadUnauthorized(): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);
        $source = self::$fileSourceClient->create($apiKey->key, md5((string) rand()));

        $filename = md5((string) rand()) . '.yaml';
        self::$fileClient->create($apiKey->key, $source->getId(), $filename, md5((string) rand()));

        self::expectException(NotFoundException::class);
        self::$fileClient->read(md5((string) rand()), $source->getId(), $filename);
    }

    public function testUpdateUnauthorized(): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);
        $source = self::$fileSourceClient->create($apiKey->key, md5((string) rand()));

        $filename = md5((string) rand()) . '.yaml';
        self::$fileClient->create($apiKey->key, $source->getId(), $filename, md5((string) rand()));

        self::expectException(NotFoundException::class);
        self::$fileClient->update(md5((string) rand()), $source->getId(), $filename, md5((string) rand()));
    }

    public function testDeleteUnauthorized(): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);
        $source = self::$fileSourceClient->create($apiKey->key, md5((string) rand()));

        $filename = md5((string) rand()) . '.yaml';
        self::$fileClient->create($apiKey->key, $source->getId(), $filename, md5((string) rand()));

        self::expectException(NotFoundException::class);
        self::$fileClient->delete(
            md5((string) rand()),
            md5((string) rand()),
            md5((string) rand()) . '.yaml'
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
        } catch (DuplicateObjectException $exception) {
        }

        self::assertInstanceOf(DuplicateObjectException::class, $exception);
        self::assertEquals(new DuplicateObjectError(new Field('filename', $filename)), $exception->error);
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

        self::expectException(NotFoundException::class);
        self::$fileClient->read($apiKey->key, $fileSource->id, $filename);
    }
}
