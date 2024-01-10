<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\FileSource;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\HttpFactory;
use SmartAssert\ApiClient\Exception\Error\BadRequestException;
use SmartAssert\ApiClient\Exception\Error\DuplicateObjectException;
use SmartAssert\ApiClient\Exception\Error\Factory as ExceptionFactory;
use SmartAssert\ApiClient\Exception\Error\ModifyReadOnlyEntityException;
use SmartAssert\ApiClient\Exception\Http\NotFoundException;
use SmartAssert\ApiClient\Exception\Http\UnauthorizedException;
use SmartAssert\ApiClient\Factory\Source\SourceFactory;
use SmartAssert\ApiClient\ServiceClient\HttpHandler;
use SmartAssert\ApiClient\ServiceClient\RequestBuilder;
use SmartAssert\ApiClient\SourceClient;
use SmartAssert\ApiClient\Tests\Integration\AbstractIntegrationTestCase;
use SmartAssert\ServiceRequest\Error\BadRequestError;
use SmartAssert\ServiceRequest\Error\DuplicateObjectError;
use SmartAssert\ServiceRequest\Error\ModifyReadOnlyEntityError;
use SmartAssert\ServiceRequest\Field\Field;
use SmartAssert\ServiceRequest\Field\Requirements;
use SmartAssert\ServiceRequest\Field\Size;
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

    public function testCreateFileSourceBadRequest(): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        $source = self::$fileSourceClient->create($apiKey->key, md5((string) rand()));

        $exception = null;
        $labelTooLong = str_repeat('.', 256);

        try {
            self::$fileSourceClient->update($apiKey->key, $source->id, $labelTooLong);
        } catch (BadRequestException $exception) {
        }

        self::assertInstanceOf(BadRequestException::class, $exception);
        self::assertEquals(
            new BadRequestError(
                (new Field('label', $labelTooLong))
                    ->withRequirements(new Requirements('string', new Size(1, 255))),
                'too_large'
            ),
            $exception->error
        );
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
        } catch (DuplicateObjectException $exception) {
        }

        self::assertInstanceOf(DuplicateObjectException::class, $exception);
        self::assertEquals(new DuplicateObjectError(new Field('label', $label)), $exception->error);
    }

    public function testUpdateDeletedSource(): void
    {
        $sourceClient = new SourceClient(
            self::$urlGenerator,
            new SourceFactory(),
            new HttpHandler(new HttpClient(), new ExceptionFactory(self::$errorDeserializer)),
            new RequestBuilder(new HttpFactory()),
        );

        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        $source = self::$fileSourceClient->create($apiKey->key, md5((string) rand()));
        $sourceClient->delete($apiKey->key, $source->id);

        $exception = null;

        try {
            self::$fileSourceClient->update($apiKey->key, $source->id, md5((string) rand()));
        } catch (ModifyReadOnlyEntityException $exception) {
        }

        self::assertInstanceOf(ModifyReadOnlyEntityException::class, $exception);
        self::assertEquals(new ModifyReadOnlyEntityError($source->id, 'file-source'), $exception->error);
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
