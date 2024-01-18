<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\GitSource;

use SmartAssert\ApiClient\Exception\Error\BadRequestException;
use SmartAssert\ApiClient\Exception\Error\DuplicateObjectException;
use SmartAssert\ApiClient\Exception\Http\UnauthorizedException;
use SmartAssert\ApiClient\Tests\Integration\AbstractIntegrationTestCase;
use SmartAssert\ServiceRequest\Error\BadRequestError;
use SmartAssert\ServiceRequest\Error\DuplicateObjectError;
use SmartAssert\ServiceRequest\Field\Field;
use SmartAssert\ServiceRequest\Field\Requirements;
use SmartAssert\ServiceRequest\Field\Size;

class CreateTest extends AbstractIntegrationTestCase
{
    use CreateDataProviderTrait;

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
     * @dataProvider createBadRequestDataProvider
     *
     * @param non-empty-string  $path
     * @param ?non-empty-string $credentials
     */
    public function testCreateBadRequest(
        string $label,
        string $hostUrl,
        string $path,
        ?string $credentials,
        BadRequestError $expected
    ): void {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        $exception = null;

        try {
            self::$gitSourceClient->create($apiKey->key, $label, $hostUrl, $path, $credentials);
        } catch (BadRequestException $exception) {
        }

        self::assertInstanceOf(BadRequestException::class, $exception);
        self::assertEquals($expected, $exception->error);
    }

    /**
     * @return array<mixed>
     */
    public static function createBadRequestDataProvider(): array
    {
        $labelTooLong = str_repeat('.', 256);
        $hostUrlTooLong = str_repeat('.', 256);

        return [
            'label empty' => [
                'label' => '',
                'hostUrl' => md5((string) rand()),
                'path' => md5((string) rand()),
                'credentials' => null,
                'expected' => new BadRequestError(
                    (new Field('label', ''))
                        ->withRequirements(new Requirements('string', new Size(1, 255))),
                    'empty'
                )
            ],
            'label too long' => [
                'label' => $labelTooLong,
                'hostUrl' => md5((string) rand()),
                'path' => md5((string) rand()),
                'credentials' => null,
                'expected' => new BadRequestError(
                    (new Field('label', $labelTooLong))
                        ->withRequirements(new Requirements('string', new Size(1, 255))),
                    'too_large'
                )
            ],
            'host url empty' => [
                'label' => md5((string) rand()),
                'hostUrl' => '',
                'path' => md5((string) rand()),
                'credentials' => null,
                'expected' => new BadRequestError(
                    (new Field('host-url', ''))
                        ->withRequirements(new Requirements('string', new Size(1, 255))),
                    'empty'
                )
            ],
            'host url too long' => [
                'label' => md5((string) rand()),
                'hostUrl' => $hostUrlTooLong,
                'path' => md5((string) rand()),
                'credentials' => null,
                'expected' => new BadRequestError(
                    (new Field('host-url', $hostUrlTooLong))
                        ->withRequirements(new Requirements('string', new Size(1, 255))),
                    'too_large'
                )
            ],
        ];
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
        } catch (DuplicateObjectException $exception) {
        }

        self::assertInstanceOf(DuplicateObjectException::class, $exception);
        self::assertEquals(new DuplicateObjectError(new Field('label', $label)), $exception->error);
    }

    /**
     * @dataProvider createDataProvider
     *
     * @param non-empty-string  $label
     * @param non-empty-string  $hostUrl
     * @param non-empty-string  $path
     * @param ?non-empty-string $credentials
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
