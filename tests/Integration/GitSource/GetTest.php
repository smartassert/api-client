<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\GitSource;

use SmartAssert\ServiceClient\Exception\NonSuccessResponseException;
use SmartAssert\ServiceClient\Exception\UnauthorizedException;
use Symfony\Component\Uid\Ulid;

class GetTest extends AbstractGitSourceTestCase
{
    use CreateDataProviderTrait;

    public function testGetUnauthorized(): void
    {
        self::expectException(UnauthorizedException::class);
        $id = (string) new Ulid();
        \assert('' !== $id);

        self::$gitSourceClient->get(md5((string) rand()), $id);
    }

    public function testGetNotFound(): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        $id = (string) new Ulid();
        \assert('' !== $id);

        self::expectException(NonSuccessResponseException::class);
        self::expectExceptionCode(404);

        self::$gitSourceClient->get($apiKey->key, $id);
    }

    /**
     * @dataProvider createDataProvider
     *
     * @param non-empty-string  $label
     * @param non-empty-string  $hostUrl
     * @param non-empty-string  $path
     * @param ?non-empty-string $credentials
     */
    public function testGetSuccess(
        string $label,
        string $hostUrl,
        string $path,
        ?string $credentials,
        bool $expectedHasCredentials
    ): void {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        $createdSource = self::$gitSourceClient->create($apiKey->key, $label, $hostUrl, $path, $credentials);
        $retrievedSource = self::$gitSourceClient->get($apiKey->key, $createdSource->id);

        self::assertSame($createdSource->id, $retrievedSource->id);
        self::assertSame($label, $retrievedSource->label);
        self::assertSame($hostUrl, $retrievedSource->hostUrl);
        self::assertSame($path, $retrievedSource->path);
        self::assertSame($expectedHasCredentials, $retrievedSource->hasCredentials);
    }
}
