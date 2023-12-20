<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\FileSource;

use SmartAssert\ApiClient\FooException\Http\NotFoundException;
use SmartAssert\ApiClient\FooException\Http\UnauthorizedException;
use SmartAssert\ApiClient\Tests\Integration\AbstractIntegrationTestCase;
use Symfony\Component\Uid\Ulid;

class UpdateTest extends AbstractIntegrationTestCase
{
    public function testUpdateUnauthorized(): void
    {
        self::expectException(UnauthorizedException::class);
        $id = (string) new Ulid();
        \assert('' !== $id);

        self::$fileSourceClient->update(md5((string) rand()), $id, md5((string) rand()));
    }

    public function testUpdateNotFound(): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        $label = md5((string) rand());

        $id = (string) new Ulid();
        \assert('' !== $id);

        self::expectException(NotFoundException::class);
        self::expectExceptionCode(404);

        self::$fileSourceClient->update($apiKey->key, $id, $label);
    }

    public function testUpdateSuccess(): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        $initialLabel = md5((string) rand());
        $createdFileSource = self::$fileSourceClient->create($apiKey->key, $initialLabel);

        $updatedLabel = md5((string) rand());
        $updatedFileSource = self::$fileSourceClient->update($apiKey->key, $createdFileSource->id, $updatedLabel);

        self::assertSame($updatedLabel, $updatedFileSource->label);
        self::assertSame($createdFileSource->id, $updatedFileSource->id);
    }
}
