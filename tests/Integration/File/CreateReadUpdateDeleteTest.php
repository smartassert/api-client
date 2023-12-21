<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\File;

use SmartAssert\ApiClient\FooException\File\DuplicateFileException;
use SmartAssert\ApiClient\FooException\File\NotFoundException;
use SmartAssert\ApiClient\FooException\Http\UnauthorizedException;

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

        self::expectException(DuplicateFileException::class);
        self::$fileClient->create($apiKey->key, $fileSource->id, $filename, $content);
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
