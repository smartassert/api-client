<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\FileSource;

use SmartAssert\ApiClient\Tests\Integration\AbstractIntegrationTestCase;
use SmartAssert\ServiceClient\Exception\NonSuccessResponseException;
use SmartAssert\ServiceClient\Exception\UnauthorizedException;
use Symfony\Component\Uid\Ulid;

class DeleteTest extends AbstractIntegrationTestCase
{
    public function testDeleteUnauthorized(): void
    {
        self::expectException(UnauthorizedException::class);
        $id = (string) new Ulid();
        \assert('' !== $id);

        self::$fileSourceClient->delete(md5((string) rand()), $id);
    }

    public function testDeleteNotFound(): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        $id = (string) new Ulid();
        \assert('' !== $id);

        self::expectException(NonSuccessResponseException::class);
        self::expectExceptionCode(404);

        self::$fileSourceClient->delete($apiKey->key, $id);
    }

    public function testDeleteSuccess(): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        $label = md5((string) rand());

        $createdFileSource = self::$fileSourceClient->create($apiKey->key, $label);
        self::assertNull($createdFileSource->deletedAt);

        $deletedFileSource = self::$fileSourceClient->delete($apiKey->key, $createdFileSource->id);
        self::assertSame($label, $deletedFileSource->label);
        self::assertSame($createdFileSource->id, $deletedFileSource->id);
        self::assertNotNull($deletedFileSource->deletedAt);
    }
}
