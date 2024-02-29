<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\Source;

use SmartAssert\ApiClient\Data\Source\FileSource;
use SmartAssert\ApiClient\Data\User\ApiKey;
use SmartAssert\ApiClient\Exception\ClientException;
use SmartAssert\ApiClient\Exception\ForbiddenException;
use SmartAssert\ApiClient\Exception\UnauthorizedException;
use SmartAssert\ApiClient\Tests\Integration\AbstractIntegrationTestCase;
use Symfony\Component\Uid\Ulid;

class GetTest extends AbstractIntegrationTestCase
{
    public function testGetUnauthorized(): void
    {
        $id = (string) new Ulid();
        \assert('' !== $id);

        $exception = null;

        try {
            self::$sourceClient->get(md5((string) rand()), $id);
        } catch (ClientException $exception) {
        }

        self::assertInstanceOf(ClientException::class, $exception);
        self::assertInstanceOf(UnauthorizedException::class, $exception->getInnerException());
    }

    public function testGetNotFound(): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        $id = (string) new Ulid();
        \assert('' !== $id);

        $exception = null;

        try {
            self::$sourceClient->get($apiKey->key, $id);
        } catch (ClientException $exception) {
        }

        self::assertInstanceOf(ClientException::class, $exception);
        self::assertInstanceOf(ForbiddenException::class, $exception->getInnerException());
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

    public function testGetFileSourceForbidden(): void
    {
        $this->doForbiddenActionTest(
            function (ApiKey $apiKey) {
                return self::$fileSourceClient->create($apiKey->key, md5((string) rand()));
            },
            function (ApiKey $apiKey, ?object $source) {
                if (!$source instanceof FileSource) {
                    return;
                }

                self::$sourceClient->get($apiKey->key, $source->id);
            },
        );
    }
}
