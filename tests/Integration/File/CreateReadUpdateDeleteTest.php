<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\File;

use SmartAssert\ApiClient\Exception\ClientException;
use SmartAssert\ApiClient\Exception\File\NotFoundException as FileNotFoundException;

class CreateReadUpdateDeleteTest extends AbstractFileTestCase
{
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
