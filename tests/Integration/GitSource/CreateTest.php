<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\GitSource;

use SmartAssert\ApiClient\Exception\ClientException;
use SmartAssert\ApiClient\Exception\Error\ErrorException;
use SmartAssert\ApiClient\Exception\UnauthorizedException;
use SmartAssert\ApiClient\Tests\Integration\AbstractIntegrationTestCase;
use SmartAssert\ServiceRequest\Error\BadRequestErrorInterface;
use SmartAssert\ServiceRequest\Error\DuplicateObjectError;
use SmartAssert\ServiceRequest\Error\DuplicateObjectErrorInterface;
use SmartAssert\ServiceRequest\Parameter\Parameter;

class CreateTest extends AbstractIntegrationTestCase
{
    use CreateUpdateGitSourceDataProviderTrait;

    public function testCreateUnauthorized(): void
    {
        $exception = null;

        try {
            self::$gitSourceClient->create(
                md5((string) rand()),
                md5((string) rand()),
                md5((string) rand()),
                md5((string) rand()),
                md5((string) rand()),
            );
        } catch (ClientException $exception) {
        }

        self::assertInstanceOf(ClientException::class, $exception);
        self::assertInstanceOf(UnauthorizedException::class, $exception->getInnerException());
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
        } catch (ClientException $exception) {
        }

        self::assertInstanceOf(ClientException::class, $exception);

        $errorException = $exception->getInnerException();
        self::assertInstanceOf(ErrorException::class, $errorException);

        $error = $errorException->getError();
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
        } catch (ClientException $exception) {
        }

        self::assertInstanceOf(ClientException::class, $exception);

        $errorException = $exception->getInnerException();
        self::assertInstanceOf(ErrorException::class, $errorException);

        $error = $errorException->getError();
        self::assertInstanceOf(DuplicateObjectErrorInterface::class, $error);
        self::assertEquals(new DuplicateObjectError(new Parameter('label', $label)), $error);
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
