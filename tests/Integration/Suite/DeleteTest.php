<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\Suite;

use SmartAssert\ApiClient\Exception\ClientException;
use SmartAssert\ApiClient\Exception\ForbiddenException;
use SmartAssert\ApiClient\Exception\UnauthorizedException;
use Symfony\Component\Uid\Ulid;

class DeleteTest extends AbstractSuiteTestCase
{
    public function testDeleteUnauthorized(): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        $source = self::$fileSourceClient->create($apiKey->key, md5((string) rand()));
        $suite = self::$suiteClient->create($apiKey->key, $source->id, md5((string) rand()), []);

        $exception = null;

        try {
            self::$suiteClient->delete(md5((string) rand()), $suite->id);
        } catch (ClientException $exception) {
        }

        self::assertInstanceOf(ClientException::class, $exception);
        self::assertInstanceOf(UnauthorizedException::class, $exception->getInnerException());
    }

    public function testDeleteSuiteNotFound(): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        $exception = null;

        $suiteId = (string) new Ulid();
        \assert('' !== $suiteId);

        try {
            self::$suiteClient->delete($apiKey->key, $suiteId);
        } catch (ClientException $exception) {
        }

        self::assertInstanceOf(ClientException::class, $exception);
        self::assertInstanceOf(ForbiddenException::class, $exception->getInnerException());
    }

    public function testDeleteSuiteForbidden(): void
    {
        $user1RefreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $user1ApiKey = self::$usersClient->getApiKey($user1RefreshableToken->token);

        $user2RefreshableToken = self::$usersClient->createToken(self::USER2_EMAIL, self::USER2_PASSWORD);
        $user2ApiKey = self::$usersClient->getApiKey($user2RefreshableToken->token);

        $source = self::$fileSourceClient->create($user2ApiKey->key, md5((string) rand()));
        $suite = self::$suiteClient->create($user2ApiKey->key, $source->id, md5((string) rand()), []);

        $exception = null;

        try {
            self::$suiteClient->delete($user1ApiKey->key, $suite->id);
        } catch (ClientException $exception) {
        }

        self::assertInstanceOf(ClientException::class, $exception);
        self::assertInstanceOf(ForbiddenException::class, $exception->getInnerException());
    }

    /**
     * @dataProvider deleteSuccessDataProvider
     *
     * @param string[] $tests
     */
    public function testDeleteSuccess(string $label, array $tests): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        $source = self::$fileSourceClient->create($apiKey->key, md5((string) rand()));

        $createdSuite = self::$suiteClient->create($apiKey->key, $source->id, $label, $tests);
        $suite = self::$suiteClient->delete($apiKey->key, $createdSuite->id);

        self::assertEquals($createdSuite->id, $suite->id);
        self::assertSame($source->id, $suite->sourceId);
        self::assertEquals($label, $suite->label);
        self::assertEquals(array_unique($tests), $suite->tests);
        self::assertNotNull($suite->deletedAt);
    }

    /**
     * @return array<mixed>
     */
    public static function deleteSuccessDataProvider(): array
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
