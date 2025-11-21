<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\Suite;

use PHPUnit\Framework\Attributes\DataProvider;
use SmartAssert\ApiClient\Exception\ClientException;
use SmartAssert\ApiClient\Exception\ForbiddenException;
use SmartAssert\ApiClient\Exception\UnauthorizedException;
use SmartAssert\ApiClient\Tests\Services\SourcesRepository;
use Symfony\Component\Uid\Ulid;

class GetTest extends AbstractSuiteTestCase
{
    public function testGetUnauthorized(): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        $source = self::$fileSourceClient->create($apiKey->key, md5((string) rand()));
        $suite = self::$suiteClient->create($apiKey->key, $source->id, md5((string) rand()), []);

        $exception = null;

        try {
            self::$suiteClient->get(md5((string) rand()), $suite->id);
        } catch (ClientException $exception) {
        }

        self::assertInstanceOf(ClientException::class, $exception);
        self::assertInstanceOf(UnauthorizedException::class, $exception->getInnerException());
    }

    public function testGetSuiteNotFound(): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        $exception = null;
        $suiteId = (string) new Ulid();

        try {
            self::$suiteClient->get($apiKey->key, $suiteId);
        } catch (ClientException $exception) {
        }

        self::assertInstanceOf(ClientException::class, $exception);
        self::assertInstanceOf(ForbiddenException::class, $exception->getInnerException());
    }

    public function testGetSuiteForbidden(): void
    {
        $user1RefreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $user1ApiKey = self::$usersClient->getApiKey($user1RefreshableToken->token);

        $user2RefreshableToken = self::$usersClient->createToken(self::USER2_EMAIL, self::USER2_PASSWORD);
        $user2ApiKey = self::$usersClient->getApiKey($user2RefreshableToken->token);

        $source = self::$fileSourceClient->create($user2ApiKey->key, md5((string) rand()));
        $suite = self::$suiteClient->create($user2ApiKey->key, $source->id, md5((string) rand()), []);

        $exception = null;

        try {
            self::$suiteClient->get($user1ApiKey->key, $suite->id);
        } catch (ClientException $exception) {
        }

        self::assertInstanceOf(ClientException::class, $exception);
        self::assertInstanceOf(ForbiddenException::class, $exception->getInnerException());
    }

    public function testGetSourceForbidden(): void
    {
        $user1RefreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $user1ApiKey = self::$usersClient->getApiKey($user1RefreshableToken->token);

        $user2RefreshableToken = self::$usersClient->createToken(self::USER2_EMAIL, self::USER2_PASSWORD);
        $user2ApiKey = self::$usersClient->getApiKey($user2RefreshableToken->token);

        $user1Source = self::$fileSourceClient->create($user1ApiKey->key, md5((string) rand()));
        $user2Source = self::$fileSourceClient->create($user2ApiKey->key, md5((string) rand()));

        $suite = self::$suiteClient->create($user1ApiKey->key, $user1Source->id, md5((string) rand()), []);

        $sourcesRepository = new SourcesRepository();
        $sourcesRepository->getConnection()->query(sprintf(
            "UPDATE public.suite SET source_id='%s' WHERE id='%s';",
            $user2Source->id,
            $suite->id
        ));

        $exception = null;

        try {
            self::$suiteClient->get($user1ApiKey->key, $suite->id);
        } catch (ClientException $exception) {
        }

        self::assertInstanceOf(ClientException::class, $exception);
        self::assertInstanceOf(ForbiddenException::class, $exception->getInnerException());
    }

    public function testGetSourceNotFound(): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        $source = self::$fileSourceClient->create($apiKey->key, md5((string) rand()));
        $createdSuite = self::$suiteClient->create($apiKey->key, $source->id, md5((string) rand()), []);

        self::$sourceClient->delete($apiKey->key, $source->id);

        $suite = self::$suiteClient->get($apiKey->key, $createdSuite->id);

        self::assertEquals($createdSuite, $suite);
    }

    /**
     * @param string[] $tests
     */
    #[DataProvider('getSuccessDataProvider')]
    public function testGetSuccess(string $label, array $tests): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        $source = self::$fileSourceClient->create($apiKey->key, md5((string) rand()));

        $createdSuite = self::$suiteClient->create($apiKey->key, $source->id, $label, $tests);
        $suite = self::$suiteClient->get($apiKey->key, $createdSuite->id);

        self::assertEquals($createdSuite->id, $suite->id);
        self::assertSame($source->id, $suite->sourceId);
        self::assertEquals($label, $suite->label);
        self::assertEquals(array_unique($tests), $suite->tests);
    }

    /**
     * @return array<mixed>
     */
    public static function getSuccessDataProvider(): array
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
