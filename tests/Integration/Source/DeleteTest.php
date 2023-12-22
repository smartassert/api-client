<?php

declare(strict_types=1);

namespace Integration\Source;

use SmartAssert\ApiClient\Exception\Http\NotFoundException;
use SmartAssert\ApiClient\Exception\Http\UnauthorizedException;
use SmartAssert\ApiClient\Tests\Integration\Source\AbstractSourceTestCase;
use Symfony\Component\Uid\Ulid;

class DeleteTest extends AbstractSourceTestCase
{
    public function testDeleteUnauthorized(): void
    {
        self::expectException(UnauthorizedException::class);
        $id = (string) new Ulid();
        \assert('' !== $id);

        self::$sourceClient->delete(md5((string) rand()), $id);
    }

    public function testDeleteNotFound(): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        $id = (string) new Ulid();
        \assert('' !== $id);

        self::expectException(NotFoundException::class);
        self::expectExceptionCode(404);

        self::$sourceClient->delete($apiKey->key, $id);
    }

    public function testDeleteFileSourceSuccess(): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        $label = md5((string) rand());

        $createdSource = self::$fileSourceClient->create($apiKey->key, $label);
        self::assertNull($createdSource->getDeletedAt());

        $retrievedSource = self::$sourceClient->delete($apiKey->key, $createdSource->id);
        self::assertSame($createdSource->getId(), $retrievedSource?->getId());
        self::assertNotNull($retrievedSource->getDeletedAt());
    }

    public function testDeleteGitSourceSuccess(): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        $label = md5((string) rand());
        $hostUrl = md5((string) rand());
        $path = md5((string) rand());

        $createdSource = self::$gitSourceClient->create($apiKey->key, $label, $hostUrl, $path, null);
        self::assertNull($createdSource->getDeletedAt());

        $retrievedSource = self::$sourceClient->delete($apiKey->key, $createdSource->id);

        self::assertSame($createdSource->getId(), $retrievedSource?->getId());
        self::assertNotNull($retrievedSource->getDeletedAt());
    }
}
