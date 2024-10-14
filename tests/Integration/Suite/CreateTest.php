<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\Suite;

use PHPUnit\Framework\Attributes\DataProvider;
use SmartAssert\ApiClient\Exception\ClientException;
use SmartAssert\ApiClient\Exception\Error\ErrorException;
use SmartAssert\ApiClient\Exception\ForbiddenException;
use SmartAssert\ApiClient\Exception\UnauthorizedException;
use SmartAssert\ServiceRequest\Error\BadRequestErrorInterface;

class CreateTest extends AbstractSuiteTestCase
{
    use CreateUpdateSuiteDataProviderTrait;

    public function testCreateUnauthorized(): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        $source = self::$fileSourceClient->create($apiKey->key, md5((string) rand()));

        $exception = null;

        try {
            self::$suiteClient->create(md5((string) rand()), $source->id, md5((string) rand()), []);
        } catch (ClientException $exception) {
        }

        self::assertInstanceOf(ClientException::class, $exception);
        self::assertInstanceOf(UnauthorizedException::class, $exception->getInnerException());
    }

    public function testCreateSourceNotFound(): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        $exception = null;

        try {
            self::$suiteClient->create($apiKey->key, md5((string) rand()), md5((string) rand()), []);
        } catch (ClientException $exception) {
        }

        self::assertInstanceOf(ClientException::class, $exception);
        self::assertInstanceOf(ForbiddenException::class, $exception->getInnerException());
    }

    public function testCreateWithSourceNotAuthorized(): void
    {
        $user1RefreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $user1ApiKey = self::$usersClient->getApiKey($user1RefreshableToken->token);

        $user2RefreshableToken = self::$usersClient->createToken(self::USER2_EMAIL, self::USER2_PASSWORD);
        $user2ApiKey = self::$usersClient->getApiKey($user2RefreshableToken->token);

        $source = self::$fileSourceClient->create($user2ApiKey->key, md5((string) rand()));

        $exception = null;

        try {
            self::$suiteClient->create($user1ApiKey->key, $source->id, md5((string) rand()), []);
        } catch (ClientException $exception) {
        }

        self::assertInstanceOf(ClientException::class, $exception);
        self::assertInstanceOf(ForbiddenException::class, $exception->getInnerException());
    }

    /**
     * @param string[] $tests
     */
    #[DataProvider('createUpdateSuiteBadRequestDataProvider')]
    public function testCreateBadRequest(
        string $label,
        array $tests,
        BadRequestErrorInterface $expected
    ): void {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        $source = self::$fileSourceClient->create($apiKey->key, md5((string) rand()));

        $exception = null;

        try {
            self::$suiteClient->create($apiKey->key, $source->id, $label, $tests);
        } catch (ClientException $exception) {
        }

        self::assertInstanceOf(ClientException::class, $exception);

        $errorException = $exception->getInnerException();
        self::assertInstanceOf(ErrorException::class, $errorException);

        $error = $errorException->getError();
        self::assertInstanceOf(BadRequestErrorInterface::class, $error);
        self::assertEquals($expected, $error);
    }

    /**
     * @param string[] $tests
     */
    #[DataProvider('createSuccessDataProvider')]
    public function testCreateSuccess(string $label, array $tests): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        $source = self::$fileSourceClient->create($apiKey->key, md5((string) rand()));

        $suite = self::$suiteClient->create($apiKey->key, $source->id, $label, $tests);

        self::assertSame($source->id, $suite->sourceId);
        self::assertEquals($label, $suite->label);
        self::assertEquals(array_unique($tests), $suite->tests);
        self::assertNull($suite->deletedAt);
    }

    /**
     * @return array<mixed>
     */
    public static function createSuccessDataProvider(): array
    {
        return [
            'empty tests' => [
                'label' => md5((string) rand()),
                'tests' => [],
            ],
            'non-empty tests' => [
                'label' => md5((string) rand()),
                'tests' => [
                    'test1.yaml',
                    'test2.yml',
                ],
            ],
        ];
    }
}
