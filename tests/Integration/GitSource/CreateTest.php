<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\GitSource;

use SmartAssert\ApiClient\Exception\ErrorExceptionInterface;
use SmartAssert\ApiClient\Exception\Http\UnauthorizedException;
use SmartAssert\ApiClient\Tests\Integration\AbstractIntegrationTestCase;
use SmartAssert\ServiceRequest\Error\BadRequestErrorInterface;
use SmartAssert\ServiceRequest\Error\DuplicateObjectError;
use SmartAssert\ServiceRequest\Error\DuplicateObjectErrorInterface;
use SmartAssert\ServiceRequest\Field\Field;

class CreateTest extends AbstractIntegrationTestCase
{
    use CreateUpdateGitSourceDataProviderTrait;

    public function testCreateUnauthorized(): void
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
     * @dataProvider badRequestDataProvider
     *
     * @param non-empty-string  $path
     * @param ?non-empty-string $credentials
     */
    public function testCreateBadRequest(
        string $label,
        string $hostUrl,
        string $path,
        ?string $credentials,
        BadRequestErrorInterface $expected
    ): void {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        $exception = null;

        try {
            self::$gitSourceClient->create($apiKey->key, $label, $hostUrl, $path, $credentials);
        } catch (ErrorExceptionInterface $exception) {
        }

        self::assertInstanceOf(ErrorExceptionInterface::class, $exception);

        $error = $exception->getError();
        self::assertInstanceOf(BadRequestErrorInterface::class, $error);
        self::assertEquals($expected, $error);
    }

    public function testCreateDuplicateLabel(): void
    {
        $label = md5((string) rand());

        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        self::$gitSourceClient->create($apiKey->key, $label, md5((string) rand()), md5((string) rand()), null);

        $exception = null;

        try {
            self::$gitSourceClient->create($apiKey->key, $label, md5((string) rand()), md5((string) rand()), null);
        } catch (ErrorExceptionInterface $exception) {
        }

        self::assertInstanceOf(ErrorExceptionInterface::class, $exception);

        $error = $exception->getError();
        self::assertInstanceOf(DuplicateObjectErrorInterface::class, $error);
        self::assertEquals(new DuplicateObjectError(new Field('label', $label)), $error);
    }

    /**
     * @dataProvider successDataProvider
     */
    public function testCreateSuccess(
        string $label,
        string $hostUrl,
        string $path,
        ?string $credentials,
        bool $expectedHasCredentials
    ): void {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        $source = self::$gitSourceClient->create($apiKey->key, $label, $hostUrl, $path, $credentials);

        self::assertNotNull($source->id);
        self::assertSame($label, $source->label);
        self::assertSame($hostUrl, $source->hostUrl);
        self::assertSame($path, $source->path);
        self::assertSame($expectedHasCredentials, $source->hasCredentials);
    }
}
