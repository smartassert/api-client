<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\File;

use SmartAssert\ApiClient\Exception\ClientException;
use SmartAssert\ApiClient\Exception\File\NotFoundException as FileNotFoundException;

class ReadTest extends AbstractFileTestCase
{
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
}
