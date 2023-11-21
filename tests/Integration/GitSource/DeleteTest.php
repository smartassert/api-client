<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\GitSource;

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

        self::$gitSourceClient->delete(md5((string) rand()), $id);
    }

    public function testDeleteNotFound(): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        $id = (string) new Ulid();
        \assert('' !== $id);

        self::expectException(NonSuccessResponseException::class);
        self::expectExceptionCode(404);

        self::$gitSourceClient->delete($apiKey->key, $id);
    }

    public function testDeleteSuccess(): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        $label = md5((string) rand());
        $hostUrl = md5((string) rand());
        $path = md5((string) rand());

        $createdSource = self::$gitSourceClient->create($apiKey->key, $label, $hostUrl, $path, null);
        self::assertNull($createdSource->deletedAt);

        $deletedSource = self::$gitSourceClient->delete($apiKey->key, $createdSource->id);
        self::assertSame($createdSource->id, $deletedSource->id);
        self::assertSame($createdSource->label, $deletedSource->label);
        self::assertSame($createdSource->hostUrl, $deletedSource->hostUrl);
        self::assertSame($createdSource->path, $deletedSource->path);
        self::assertSame($createdSource->hasCredentials, $deletedSource->hasCredentials);
        self::assertNotNull($deletedSource->deletedAt);
    }
}
