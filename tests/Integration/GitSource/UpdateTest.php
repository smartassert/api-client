<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\GitSource;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\HttpFactory;
use SmartAssert\ApiClient\Exception\Error\Factory as ExceptionFactory;
use SmartAssert\ApiClient\Exception\ErrorExceptionInterface;
use SmartAssert\ApiClient\Exception\Http\UnauthorizedException;
use SmartAssert\ApiClient\Factory\Source\SourceFactory;
use SmartAssert\ApiClient\ServiceClient\HttpHandler;
use SmartAssert\ApiClient\SourceClient;
use SmartAssert\ApiClient\Tests\Integration\AbstractIntegrationTestCase;
use SmartAssert\ServiceRequest\Error\BadRequestErrorInterface;
use SmartAssert\ServiceRequest\Error\DuplicateObjectError;
use SmartAssert\ServiceRequest\Error\DuplicateObjectErrorInterface;
use SmartAssert\ServiceRequest\Error\ModifyReadOnlyEntityError;
use SmartAssert\ServiceRequest\Error\ModifyReadOnlyEntityErrorInterface;
use SmartAssert\ServiceRequest\Parameter\Parameter;

class UpdateTest extends AbstractIntegrationTestCase
{
    use CreateUpdateGitSourceDataProviderTrait;

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
     * @dataProvider badRequestDataProvider
     */
    public function testUpdateBadRequest(
        string $label,
        string $hostUrl,
        string $path,
        ?string $credentials,
        BadRequestErrorInterface $expected
    ): void {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        $source = self::$gitSourceClient->create(
            $apiKey->key,
            md5((string) rand()),
            md5((string) rand()),
            md5((string) rand()),
            null
        );

        $exception = null;

        try {
            self::$gitSourceClient->update($apiKey->key, $source->id, $label, $hostUrl, $path, $credentials);
        } catch (ErrorExceptionInterface $exception) {
        }

        self::assertInstanceOf(ErrorExceptionInterface::class, $exception);

        $error = $exception->getError();
        self::assertInstanceOf(BadRequestErrorInterface::class, $error);
        self::assertEquals($expected, $error);
    }

    public function testUpdateDuplicateLabel(): void
    {
        $label = md5((string) rand());
        $conflictLabel = md5((string) rand());

        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        $source = self::$gitSourceClient->create(
            $apiKey->key,
            $label,
            md5((string) rand()),
            md5((string) rand()),
            null
        );
        self::$gitSourceClient->create($apiKey->key, $conflictLabel, md5((string) rand()), md5((string) rand()), null);

        $exception = null;

        try {
            self::$gitSourceClient->update(
                $apiKey->key,
                $source->id,
                $conflictLabel,
                md5((string) rand()),
                md5((string) rand()),
                null
            );
        } catch (ErrorExceptionInterface $exception) {
        }

        self::assertInstanceOf(ErrorExceptionInterface::class, $exception);

        $error = $exception->getError();
        self::assertInstanceOf(DuplicateObjectErrorInterface::class, $error);
        self::assertEquals(new DuplicateObjectError(new Parameter('label', $conflictLabel)), $error);
    }

    public function testUpdateDeletedSource(): void
    {
        $sourceClient = new SourceClient(
            new SourceFactory(),
            new HttpHandler(
                new HttpClient(),
                new ExceptionFactory(self::$errorDeserializer),
                new HttpFactory(),
                self::$urlGenerator,
            ),
        );

        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        $source = self::$gitSourceClient->create(
            $apiKey->key,
            md5((string) rand()),
            md5((string) rand()),
            md5((string) rand()),
            null
        );
        $sourceClient->delete($apiKey->key, $source->id);

        $exception = null;

        try {
            self::$gitSourceClient->update(
                $apiKey->key,
                $source->id,
                md5((string) rand()),
                md5((string) rand()),
                md5((string) rand()),
                null
            );
        } catch (ErrorExceptionInterface $exception) {
        }

        self::assertInstanceOf(ErrorExceptionInterface::class, $exception);

        $error = $exception->getError();
        self::assertInstanceOf(ModifyReadOnlyEntityErrorInterface::class, $error);
        self::assertEquals(new ModifyReadOnlyEntityError($source->id, 'git-source'), $error);
    }

    /**
     * @dataProvider successDataProvider
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
