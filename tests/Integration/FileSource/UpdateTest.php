<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\FileSource;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\HttpFactory;
use PHPUnit\Framework\Attributes\DataProvider;
use SmartAssert\ApiClient\Data\Source\FileSource;
use SmartAssert\ApiClient\Data\User\ApiKey;
use SmartAssert\ApiClient\Exception\ClientException;
use SmartAssert\ApiClient\Exception\Error\ErrorException;
use SmartAssert\ApiClient\Exception\Error\Factory as ExceptionFactory;
use SmartAssert\ApiClient\Exception\ForbiddenException;
use SmartAssert\ApiClient\Exception\UnauthorizedException;
use SmartAssert\ApiClient\Factory\Source\SourceFactory;
use SmartAssert\ApiClient\ServiceClient\HttpHandler;
use SmartAssert\ApiClient\SourceClient;
use SmartAssert\ApiClient\Tests\Integration\AbstractIntegrationTestCase;
use SmartAssert\ServiceRequest\Error\BadRequestError;
use SmartAssert\ServiceRequest\Error\BadRequestErrorInterface;
use SmartAssert\ServiceRequest\Error\DuplicateObjectError;
use SmartAssert\ServiceRequest\Error\DuplicateObjectErrorInterface;
use SmartAssert\ServiceRequest\Error\ModifyReadOnlyEntityError;
use SmartAssert\ServiceRequest\Error\ModifyReadOnlyEntityErrorInterface;
use SmartAssert\ServiceRequest\Parameter\Parameter;
use Symfony\Component\Uid\Ulid;

class UpdateTest extends AbstractIntegrationTestCase
{
    use CreateUpdateFileSourceDataProviderTrait;

    public function testUpdateUnauthorized(): void
    {
        $id = (string) new Ulid();
        \assert('' !== $id);

        $exception = null;

        try {
            self::$fileSourceClient->update(md5((string) rand()), $id, md5((string) rand()));
        } catch (ClientException $exception) {
        }

        self::assertInstanceOf(ClientException::class, $exception);
        self::assertInstanceOf(UnauthorizedException::class, $exception->getInnerException());
    }

    public function testUpdateNotFound(): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        $label = md5((string) rand());

        $id = (string) new Ulid();
        \assert('' !== $id);

        $exception = null;

        try {
            self::$fileSourceClient->update($apiKey->key, $id, $label);
        } catch (ClientException $exception) {
        }

        self::assertInstanceOf(ClientException::class, $exception);
        self::assertInstanceOf(ForbiddenException::class, $exception->getInnerException());
    }

    #[DataProvider('createUpdateFileSourceBadRequestDataProvider')]
    public function testUpdateBadRequest(string $label, BadRequestError $expected): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        $source = self::$fileSourceClient->create($apiKey->key, md5((string) rand()));

        $exception = null;

        try {
            self::$fileSourceClient->update($apiKey->key, $source->id, $label);
        } catch (ClientException $exception) {
        }

        self::assertInstanceOf(ClientException::class, $exception);

        $errorException = $exception->getInnerException();
        self::assertInstanceOf(ErrorException::class, $errorException);

        $error = $errorException->getError();
        self::assertInstanceOf(BadRequestErrorInterface::class, $error);
        self::assertEquals($expected, $error);
    }

    public function testUpdateDuplicateLabel(): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        $createdFileSource = self::$fileSourceClient->create($apiKey->key, md5((string) rand()));

        $label = md5((string) rand());
        self::$fileSourceClient->create($apiKey->key, $label);

        $exception = null;

        try {
            self::$fileSourceClient->update($apiKey->key, $createdFileSource->id, $label);
        } catch (ClientException $exception) {
        }

        self::assertInstanceOf(ClientException::class, $exception);

        $errorException = $exception->getInnerException();
        self::assertInstanceOf(ErrorException::class, $errorException);

        $error = $errorException->getError();
        self::assertInstanceOf(DuplicateObjectErrorInterface::class, $error);
        self::assertEquals(new DuplicateObjectError(new Parameter('label', $label)), $error);
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

        $source = self::$fileSourceClient->create($apiKey->key, md5((string) rand()));
        $sourceClient->delete($apiKey->key, $source->id);

        $exception = null;

        try {
            self::$fileSourceClient->update($apiKey->key, $source->id, md5((string) rand()));
        } catch (ClientException $exception) {
        }

        self::assertInstanceOf(ClientException::class, $exception);

        $errorException = $exception->getInnerException();
        self::assertInstanceOf(ErrorException::class, $errorException);

        $error = $errorException->getError();
        self::assertInstanceOf(ModifyReadOnlyEntityErrorInterface::class, $error);
        self::assertEquals(new ModifyReadOnlyEntityError($source->id, 'file-source'), $error);
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

    public function testUpdateFileSourceForbidden(): void
    {
        $this->doForbiddenActionTest(
            function (ApiKey $apiKey) {
                return self::$fileSourceClient->create($apiKey->key, md5((string) rand()));
            },
            function (ApiKey $apiKey, ?object $source) {
                if (!$source instanceof FileSource) {
                    return;
                }

                self::$fileSourceClient->update($apiKey->key, $source->id, md5((string) rand()));
            },
        );
    }
}
