<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\Source;

use SmartAssert\ApiClient\Exception\Http\NotFoundException;
use SmartAssert\ApiClient\Exception\Http\UnauthorizedException;
use Symfony\Component\Uid\Ulid;

class GetTest extends AbstractSourceTestCase
{
    public function testGetUnauthorized(): void
    {
        self::expectException(UnauthorizedException::class);
        $id = (string) new Ulid();
        \assert('' !== $id);

        self::$sourceClient->get(md5((string) rand()), $id);
    }

    public function testGetNotFound(): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        $id = (string) new Ulid();
        \assert('' !== $id);

        self::expectException(NotFoundException::class);
        self::expectExceptionCode(404);

        self::$sourceClient->get($apiKey->key, $id);
    }

    public function testGetFileSourceSuccess(): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        $label = md5((string) rand());

        $createdSource = self::$fileSourceClient->create($apiKey->key, $label);
        $retrievedSource = self::$sourceClient->get($apiKey->key, $createdSource->id);

        self::assertEquals($createdSource, $retrievedSource);
    }

    public function testGetGitSourceSuccess(): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        $label = md5((string) rand());
        $hostUrl = md5((string) rand());
        $path = md5((string) rand());

        $createdSource = self::$gitSourceClient->create($apiKey->key, $label, $hostUrl, $path, null);
        $retrievedSource = self::$sourceClient->get($apiKey->key, $createdSource->id);

        self::assertEquals($createdSource, $retrievedSource);
    }
}
