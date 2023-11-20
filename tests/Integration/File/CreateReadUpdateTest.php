<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\File;

use SmartAssert\ApiClient\Exception\DuplicateFileException;
use SmartAssert\ServiceClient\Exception\NonSuccessResponseException;

class CreateReadUpdateTest extends AbstractFileTestCase
{
    public function testCreateUnauthorized(): void
    {
        try {
            self::$fileClient->create(
                md5((string) rand()),
                md5((string) rand()),
                md5((string) rand()) . '.yaml',
                md5((string) rand())
            );
            self::fail(NonSuccessResponseException::class . ' not thrown');
        } catch (NonSuccessResponseException $e) {
            self::assertSame(404, $e->getStatusCode());
        }
    }

    public function testReadUnauthorized(): void
    {
        try {
            self::$fileClient->read(
                md5((string) rand()),
                md5((string) rand()),
                md5((string) rand()) . '.yaml'
            );
            self::fail(NonSuccessResponseException::class . ' not thrown');
        } catch (NonSuccessResponseException $e) {
            self::assertSame(404, $e->getStatusCode());
        }
    }

    public function testUpdateUnauthorized(): void
    {
        try {
            self::$fileClient->update(
                md5((string) rand()),
                md5((string) rand()),
                md5((string) rand()) . '.yaml',
                md5((string) rand())
            );
            self::fail(NonSuccessResponseException::class . ' not thrown');
        } catch (NonSuccessResponseException $e) {
            self::assertSame(404, $e->getStatusCode());
        }
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

    public function testCreateReadUpdateSuccess(): void
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
    }
}
