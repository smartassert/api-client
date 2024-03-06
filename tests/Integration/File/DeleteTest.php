<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\File;

use SmartAssert\ApiClient\Exception\ClientException;
use SmartAssert\ApiClient\Exception\File\NotFoundException as FileNotFoundException;
use SmartAssert\ApiClient\Exception\ForbiddenException;

class DeleteTest extends AbstractFileTestCase
{
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

    public function testDeleteSourceForbidden(): void
    {
        $user1RefreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $user1ApiKey = self::$usersClient->getApiKey($user1RefreshableToken->token);

        $user2RefreshableToken = self::$usersClient->createToken(self::USER2_EMAIL, self::USER2_PASSWORD);
        $user2ApiKey = self::$usersClient->getApiKey($user2RefreshableToken->token);

        $source = self::$fileSourceClient->create($user2ApiKey->key, md5((string) rand()));

        $filename = md5((string) rand()) . '.yaml';
        self::$fileClient->create($user2ApiKey->key, $source->id, $filename, md5((string) rand()));

        $exception = null;

        try {
            self::$fileClient->delete($user1ApiKey->key, $source->getId(), $filename);
        } catch (ClientException $exception) {
        }

        self::assertInstanceOf(ClientException::class, $exception);
        self::assertInstanceOf(ForbiddenException::class, $exception->getInnerException());
    }
}
