<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\GitSource;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\HttpFactory;
use SmartAssert\ApiClient\Exception\Error\BadRequestException;
use SmartAssert\ApiClient\Exception\Error\DuplicateObjectException;
use SmartAssert\ApiClient\Exception\Error\Factory as ExceptionFactory;
use SmartAssert\ApiClient\Exception\Error\ModifyReadOnlyEntityException;
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

    public function testCreateBadRequest(): void
    {
        $label = str_repeat('.', 256);

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
            self::$gitSourceClient->update(
                $apiKey->key,
                $source->id,
                $label,
                md5((string) rand()),
                md5((string) rand()),
                null
            );
        } catch (BadRequestException $exception) {
        }

        self::assertInstanceOf(BadRequestException::class, $exception);
        self::assertEquals(
            new BadRequestError(
                (new Field('label', $label))
                    ->withRequirements(new Requirements('string', new Size(1, 255))),
                'too_large'
            ),
            $exception->error
        );
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
        } catch (DuplicateObjectException $exception) {
        }

        self::assertInstanceOf(DuplicateObjectException::class, $exception);
        self::assertEquals(new DuplicateObjectError(new Field('label', $conflictLabel)), $exception->error);
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
        } catch (ModifyReadOnlyEntityException $exception) {
        }

        self::assertInstanceOf(ModifyReadOnlyEntityException::class, $exception);
        self::assertEquals(new ModifyReadOnlyEntityError($source->id, 'git-source'), $exception->error);
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
