<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\Suite;

use SmartAssert\ApiClient\Data\Source\SourceInterface;
use SmartAssert\ApiClient\Data\Source\Suite;
use SmartAssert\ApiClient\Data\User\ApiKey;
use SmartAssert\ApiClient\Exception\ClientException;
use SmartAssert\ApiClient\Exception\Error\ErrorException;
use SmartAssert\ApiClient\Exception\ForbiddenException;
use SmartAssert\ApiClient\Exception\UnauthorizedException;
use SmartAssert\ApiClient\FileSourceClient;
use SmartAssert\ApiClient\GitSourceClient;
use SmartAssert\ApiClient\SuiteClient;
use SmartAssert\ServiceRequest\Error\BadRequestErrorInterface;
use SmartAssert\ServiceRequest\Error\ModifyReadOnlyEntityError;
use Symfony\Component\Uid\Ulid;

class UpdateTest extends AbstractSuiteTestCase
{
    use CreateUpdateSuiteDataProviderTrait;

    public function testUpdateUnauthorized(): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        $source = self::$fileSourceClient->create($apiKey->key, md5((string) rand()));
        $suite = self::$suiteClient->create($apiKey->key, $source->id, md5((string) rand()), []);

        $exception = null;

        try {
            self::$suiteClient->update(md5((string) rand()), $suite->id, $source->id, md5((string) rand()), []);
        } catch (ClientException $exception) {
        }

        self::assertInstanceOf(ClientException::class, $exception);
        self::assertInstanceOf(UnauthorizedException::class, $exception->getInnerException());
    }

    public function testUpdateSourceNotFound(): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        $source = self::$fileSourceClient->create($apiKey->key, md5((string) rand()));
        $suite = self::$suiteClient->create($apiKey->key, $source->id, md5((string) rand()), []);

        $sourceId = (string) new Ulid();
        \assert('' !== $sourceId);

        $exception = null;

        try {
            self::$suiteClient->update($apiKey->key, $suite->id, $sourceId, md5((string) rand()), []);
        } catch (ClientException $exception) {
        }

        self::assertInstanceOf(ClientException::class, $exception);
        self::assertInstanceOf(ForbiddenException::class, $exception->getInnerException());
    }

    public function testUpdateSuiteNotFound(): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        $source = self::$fileSourceClient->create($apiKey->key, md5((string) rand()));

        $suiteId = (string) new Ulid();
        \assert('' !== $suiteId);

        $exception = null;

        try {
            self::$suiteClient->update($apiKey->key, $suiteId, $source->id, md5((string) rand()), []);
        } catch (ClientException $exception) {
        }

        self::assertInstanceOf(ClientException::class, $exception);
        self::assertInstanceOf(ForbiddenException::class, $exception->getInnerException());
    }

    public function testUpdateDeletedSuite(): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        $source = self::$fileSourceClient->create($apiKey->key, md5((string) rand()));
        $suite = self::$suiteClient->create($apiKey->key, $source->id, md5((string) rand()), []);

        self::$suiteClient->delete($apiKey->key, $suite->id);

        $exception = null;

        try {
            self::$suiteClient->update($apiKey->key, $suite->id, $source->id, md5((string) rand()), []);
        } catch (ClientException $exception) {
        }

        self::assertInstanceOf(ClientException::class, $exception);

        $errorException = $exception->getInnerException();
        self::assertInstanceOf(ErrorException::class, $errorException);
        self::assertEquals(new ModifyReadOnlyEntityError($suite->id, 'suite'), $errorException->getError());
    }

    public function testUpdateSuiteForbidden(): void
    {
        $user1RefreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $user1ApiKey = self::$usersClient->getApiKey($user1RefreshableToken->token);

        $user2RefreshableToken = self::$usersClient->createToken(self::USER2_EMAIL, self::USER2_PASSWORD);
        $user2ApiKey = self::$usersClient->getApiKey($user2RefreshableToken->token);

        $user1Source = self::$fileSourceClient->create($user1ApiKey->key, md5((string) rand()));
        $user2Source = self::$fileSourceClient->create($user2ApiKey->key, md5((string) rand()));
        $user2Suite = self::$suiteClient->create($user2ApiKey->key, $user2Source->id, md5((string) rand()), []);

        $exception = null;

        try {
            self::$suiteClient->update($user1ApiKey->key, $user2Suite->id, $user1Source->id, md5((string) rand()), []);
        } catch (ClientException $exception) {
        }

        self::assertInstanceOf(ClientException::class, $exception);
        self::assertInstanceOf(ForbiddenException::class, $exception->getInnerException());
    }

    public function testUpdateSourceForbidden(): void
    {
        $user1RefreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $user1ApiKey = self::$usersClient->getApiKey($user1RefreshableToken->token);

        $user2RefreshableToken = self::$usersClient->createToken(self::USER2_EMAIL, self::USER2_PASSWORD);
        $user2ApiKey = self::$usersClient->getApiKey($user2RefreshableToken->token);

        $user1Source = self::$fileSourceClient->create($user1ApiKey->key, md5((string) rand()));
        $user2Source = self::$fileSourceClient->create($user2ApiKey->key, md5((string) rand()));
        $user1Suite = self::$suiteClient->create($user1ApiKey->key, $user1Source->id, md5((string) rand()), []);

        $exception = null;

        try {
            self::$suiteClient->update($user1ApiKey->key, $user1Suite->id, $user2Source->id, md5((string) rand()), []);
        } catch (ClientException $exception) {
        }

        self::assertInstanceOf(ClientException::class, $exception);
        self::assertInstanceOf(ForbiddenException::class, $exception->getInnerException());
    }

    /**
     * @dataProvider createUpdateSuiteBadRequestDataProvider
     *
     * @param string[] $tests
     */
    public function testUpdateBadRequest(
        string $label,
        array $tests,
        BadRequestErrorInterface $expected
    ): void {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        $source = self::$fileSourceClient->create($apiKey->key, md5((string) rand()));
        $suite = self::$suiteClient->create($apiKey->key, $source->id, md5((string) rand()), []);

        $exception = null;

        try {
            self::$suiteClient->update($apiKey->key, $suite->id, $source->id, $label, $tests);
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
     * @dataProvider updateSuccessDataProvider
     *
     * @param callable(ApiKey, FileSourceClient, GitSourceClient): SourceInterface                  $sourceCreator
     * @param callable(ApiKey, SuiteClient, SourceInterface): Suite                                 $suiteCreator
     * @param callable(ApiKey, SourceInterface, FileSourceClient, GitSourceClient): SourceInterface $updateSourceCreator
     * @param callable(string, string): Suite                                                       $expectedCreator
     * @param string[]                                                                              $tests
     */
    public function testUpdateSuccess(
        callable $sourceCreator,
        callable $suiteCreator,
        callable $updateSourceCreator,
        string $label,
        array $tests,
        callable $expectedCreator
    ): void {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        $source = $sourceCreator($apiKey, self::$fileSourceClient, self::$gitSourceClient);
        $createdSuite = $suiteCreator($apiKey, self::$suiteClient, $source);

        $updateSource = $updateSourceCreator($apiKey, $source, self::$fileSourceClient, self::$gitSourceClient);

        $suite = self::$suiteClient->update($apiKey->key, $createdSuite->id, $updateSource->getId(), $label, $tests);
        $expected = $expectedCreator($suite->id, $updateSource->getId());

        self::assertEquals($expected, $suite);
    }

    /**
     * @return array<mixed>
     */
    public static function updateSuccessDataProvider(): array
    {
        $label1 = md5((string) rand());
        $label2 = md5((string) rand());
        $label3 = md5((string) rand());
        $label4 = md5((string) rand());
        $label5 = md5((string) rand());
        $label6 = md5((string) rand());
        $label7 = md5((string) rand());
        $label8 = md5((string) rand());

        return [
            'file source, no source change, no label change, no tests, no test change' => [
                'sourceCreator' => function (ApiKey $apiKey, FileSourceClient $fileSourceClient) {
                    return $fileSourceClient->create($apiKey->key, md5((string) rand()));
                },
                'suiteCreator' => function (
                    ApiKey $apiKey,
                    SuiteClient $suiteClient,
                    SourceInterface $source
                ) use (
                    $label1
                ) {
                    return $suiteClient->create($apiKey->key, $source->getId(), $label1, []);
                },
                'updateSourceCreator' => function (ApiKey $apiKey, SourceInterface $source) {
                    return $source;
                },
                'label' => $label1,
                'tests' => [],
                'expected' => function (string $suiteId, string $sourceId) use ($label1) {
                    \assert('' !== $suiteId);

                    return new Suite($suiteId, $sourceId, $label1, [], null);
                },
            ],
            'file source, has source change, no label change, no tests, no test change' => [
                'sourceCreator' => function (ApiKey $apiKey, FileSourceClient $fileSourceClient) {
                    return $fileSourceClient->create($apiKey->key, md5((string) rand()));
                },
                'suiteCreator' => function (
                    ApiKey $apiKey,
                    SuiteClient $suiteClient,
                    SourceInterface $source
                ) use (
                    $label2
                ) {
                    return $suiteClient->create($apiKey->key, $source->getId(), $label2, []);
                },
                'updateSourceCreator' => function (
                    ApiKey $apiKey,
                    SourceInterface $source,
                    FileSourceClient $fileSourceClient
                ) {
                    return $fileSourceClient->create($apiKey->key, md5((string) rand()));
                },
                'label' => $label2,
                'tests' => [],
                'expected' => function (string $suiteId, string $sourceId) use ($label2) {
                    \assert('' !== $suiteId);

                    return new Suite($suiteId, $sourceId, $label2, [], null);
                },
            ],
            'file source, no source change, has label change, no tests, no test change' => [
                'sourceCreator' => function (ApiKey $apiKey, FileSourceClient $fileSourceClient) {
                    return $fileSourceClient->create($apiKey->key, md5((string) rand()));
                },
                'suiteCreator' => function (
                    ApiKey $apiKey,
                    SuiteClient $suiteClient,
                    SourceInterface $source
                ) use (
                    $label3
                ) {
                    return $suiteClient->create($apiKey->key, $source->getId(), $label3, []);
                },
                'updateSourceCreator' => function (ApiKey $apiKey, SourceInterface $source) {
                    return $source;
                },
                'label' => $label4,
                'tests' => [],
                'expected' => function (string $suiteId, string $sourceId) use ($label4) {
                    \assert('' !== $suiteId);

                    return new Suite($suiteId, $sourceId, $label4, [], null);
                },
            ],
            'file source, no source change, no label change, no tests, has test change' => [
                'sourceCreator' => function (ApiKey $apiKey, FileSourceClient $fileSourceClient) {
                    return $fileSourceClient->create($apiKey->key, md5((string) rand()));
                },
                'suiteCreator' => function (
                    ApiKey $apiKey,
                    SuiteClient $suiteClient,
                    SourceInterface $source
                ) use (
                    $label5
                ) {
                    return $suiteClient->create($apiKey->key, $source->getId(), $label5, []);
                },
                'updateSourceCreator' => function (ApiKey $apiKey, SourceInterface $source) {
                    return $source;
                },
                'label' => $label5,
                'tests' => ['test1.yaml', 'test2.yml'],
                'expected' => function (string $suiteId, string $sourceId) use ($label5) {
                    \assert('' !== $suiteId);

                    return new Suite($suiteId, $sourceId, $label5, ['test1.yaml', 'test2.yml'], null);
                },
            ],
            'file source, no source change, no label change, has tests, change tests' => [
                'sourceCreator' => function (ApiKey $apiKey, FileSourceClient $fileSourceClient) {
                    return $fileSourceClient->create($apiKey->key, md5((string) rand()));
                },
                'suiteCreator' => function (
                    ApiKey $apiKey,
                    SuiteClient $suiteClient,
                    SourceInterface $source
                ) use (
                    $label6
                ) {
                    return $suiteClient->create($apiKey->key, $source->getId(), $label6, ['test3.yaml']);
                },
                'updateSourceCreator' => function (ApiKey $apiKey, SourceInterface $source) {
                    return $source;
                },
                'label' => $label6,
                'tests' => ['test4.yaml', 'test5.yml'],
                'expected' => function (string $suiteId, string $sourceId) use ($label6) {
                    \assert('' !== $suiteId);

                    return new Suite($suiteId, $sourceId, $label6, ['test4.yaml', 'test5.yml'], null);
                },
            ],
            'file source, no source change, no label change, has tests, remove tests' => [
                'sourceCreator' => function (ApiKey $apiKey, FileSourceClient $fileSourceClient) {
                    return $fileSourceClient->create($apiKey->key, md5((string) rand()));
                },
                'suiteCreator' => function (
                    ApiKey $apiKey,
                    SuiteClient $suiteClient,
                    SourceInterface $source
                ) use (
                    $label7
                ) {
                    return $suiteClient->create($apiKey->key, $source->getId(), $label7, ['test3.yaml']);
                },
                'updateSourceCreator' => function (ApiKey $apiKey, SourceInterface $source) {
                    return $source;
                },
                'label' => $label7,
                'tests' => [],
                'expected' => function (string $suiteId, string $sourceId) use ($label7) {
                    \assert('' !== $suiteId);

                    return new Suite($suiteId, $sourceId, $label7, [], null);
                },
            ],
            'file source change to git source' => [
                'sourceCreator' => function (ApiKey $apiKey, FileSourceClient $fileSourceClient) {
                    return $fileSourceClient->create($apiKey->key, md5((string) rand()));
                },
                'suiteCreator' => function (
                    ApiKey $apiKey,
                    SuiteClient $suiteClient,
                    SourceInterface $source
                ) use (
                    $label8
                ) {
                    return $suiteClient->create($apiKey->key, $source->getId(), $label8, ['test3.yaml']);
                },
                'updateSourceCreator' => function (
                    ApiKey $apiKey,
                    SourceInterface $source,
                    FileSourceClient $fileSourceClient,
                    GitSourceClient $gitSourceClient
                ) {
                    return $gitSourceClient->create(
                        $apiKey->key,
                        md5((string) rand()),
                        md5((string) rand()),
                        md5((string) rand()),
                        null
                    );
                },
                'label' => $label8,
                'tests' => [],
                'expected' => function (string $suiteId, string $sourceId) use ($label8) {
                    \assert('' !== $suiteId);

                    return new Suite($suiteId, $sourceId, $label8, [], null);
                },
            ],
        ];
    }
}
