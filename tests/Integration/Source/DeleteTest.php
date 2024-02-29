<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\Source;

use SmartAssert\ApiClient\Exception\ClientException;
use SmartAssert\ApiClient\Exception\ForbiddenException;
use SmartAssert\ApiClient\Exception\UnauthorizedException;
use SmartAssert\ApiClient\Tests\Integration\AbstractIntegrationTestCase;
use Symfony\Component\Uid\Ulid;

class DeleteTest extends AbstractIntegrationTestCase
{
    public function testDeleteUnauthorized(): void
    {
        $id = (string) new Ulid();
        \assert('' !== $id);

        $exception = null;

        try {
            self::$sourceClient->delete(md5((string) rand()), $id);
        } catch (ClientException $exception) {
        }

        self::assertInstanceOf(ClientException::class, $exception);
        self::assertInstanceOf(UnauthorizedException::class, $exception->getInnerException());
    }

    public function testDeleteNotFound(): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        $id = (string) new Ulid();
        \assert('' !== $id);

        $exception = null;

        try {
            self::$sourceClient->delete($apiKey->key, $id);
        } catch (ClientException $exception) {
        }

        self::assertInstanceOf(ClientException::class, $exception);
        self::assertInstanceOf(ForbiddenException::class, $exception->getInnerException());
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
