<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\FileSource;

use SmartAssert\ServiceClient\Exception\NonSuccessResponseException;
use SmartAssert\ServiceClient\Exception\UnauthorizedException;
use Symfony\Component\Uid\Ulid;

class GetTest extends AbstractFileSourceTestCase
{
    public function testGetUnauthorized(): void
    {
        self::expectException(UnauthorizedException::class);
        $id = (string) new Ulid();
        \assert('' !== $id);

        self::$fileSourceClient->get(md5((string) rand()), $id);
    }

    public function testGetNotFound(): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        $label = md5((string) rand());

        $id = (string) new Ulid();
        \assert('' !== $id);

        self::expectException(NonSuccessResponseException::class);
        self::expectExceptionCode(404);

        self::$fileSourceClient->get($apiKey->key, $id);
    }

    public function testGetSuccess(): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        $label = md5((string) rand());

        $createdFileSource = self::$fileSourceClient->create($apiKey->key, $label);
        $retrievedFileSource = self::$fileSourceClient->get($apiKey->key, $createdFileSource->id);

        self::assertSame($label, $retrievedFileSource->label);
        self::assertSame($createdFileSource->id, $retrievedFileSource->id);
    }
}
