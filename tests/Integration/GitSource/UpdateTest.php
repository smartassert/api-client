<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\GitSource;

use SmartAssert\ApiClient\Tests\Integration\AbstractIntegrationTestCase;
use SmartAssert\ServiceClient\Exception\UnauthorizedException;

class UpdateTest extends AbstractIntegrationTestCase
{
    use CreateDataProviderTrait;

    public function testUpdateUnauthorized(): void
    {
        self::expectException(UnauthorizedException::class);

        self::$gitSourceClient->create(
            md5((string) rand()),
            md5((string) rand()),
            md5((string) rand()),
            md5((string) rand()),
            md5((string) rand()),
        );
    }

    /**
     * @dataProvider createDataProvider
     *
     * @param non-empty-string  $label
     * @param non-empty-string  $hostUrl
     * @param non-empty-string  $path
     * @param ?non-empty-string $credentials
     */
    public function testUpdateSuccess(
        string $label,
        string $hostUrl,
        string $path,
        ?string $credentials,
        bool $expectedHasCredentials
    ): void {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        $createdSource = self::$gitSourceClient->create($apiKey->key, $label, $hostUrl, $path, $credentials);
        $updatedSource = self::$gitSourceClient->update(
            $apiKey->key,
            $createdSource->id,
            $label,
            $hostUrl,
            $path,
            $credentials
        );

        self::assertNotNull($createdSource->id);
        self::assertSame($createdSource->id, $updatedSource->id);
        self::assertSame($createdSource->label, $updatedSource->label);
        self::assertSame($createdSource->hostUrl, $updatedSource->hostUrl);
        self::assertSame($createdSource->path, $updatedSource->path);
        self::assertSame($expectedHasCredentials, $updatedSource->hasCredentials);
    }
}
