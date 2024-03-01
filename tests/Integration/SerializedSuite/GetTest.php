<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\SerializedSuite;

use SmartAssert\ApiClient\Data\Source\Suite;
use SmartAssert\ApiClient\Data\User\ApiKey;
use SmartAssert\ApiClient\Exception\ClientException;
use SmartAssert\ApiClient\Exception\ForbiddenException;
use SmartAssert\ApiClient\Exception\UnauthorizedException;
use SmartAssert\ApiClient\FileSourceClient;
use SmartAssert\ApiClient\GitSourceClient;
use SmartAssert\ApiClient\SuiteClient;
use Symfony\Component\Uid\Ulid;

class GetTest extends AbstractSerializedSuiteTestCase
{
    public function testGetUnauthorized(): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        $source = self::$fileSourceClient->create($apiKey->key, md5((string) rand()));
        $suite = self::$suiteClient->create($apiKey->key, $source->id, md5((string) rand()), []);

        $serializedSuiteId = (string) new Ulid();
        \assert('' !== $serializedSuiteId);

        $serializedSuite = self::$serializedSuiteClient->create($apiKey->key, $suite->id, $serializedSuiteId, []);

        $exception = null;

        try {
            self::$serializedSuiteClient->get(md5((string) rand()), $serializedSuite->id);
        } catch (ClientException $exception) {
        }

        self::assertInstanceOf(ClientException::class, $exception);
        self::assertInstanceOf(UnauthorizedException::class, $exception->getInnerException());
    }

    public function testGetSerializedSuiteNotFound(): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        $serializedSuiteId = (string) new Ulid();
        \assert('' !== $serializedSuiteId);

        $exception = null;

        try {
            self::$serializedSuiteClient->get($apiKey->key, $serializedSuiteId);
        } catch (ClientException $exception) {
        }

        self::assertInstanceOf(ClientException::class, $exception);
        self::assertInstanceOf(ForbiddenException::class, $exception->getInnerException());
    }

    /**
     * @dataProvider getSuccessDataProvider
     *
     * @param callable(ApiKey, FileSourceClient, GitSourceClient, SuiteClient): Suite $suiteCreator
     * @param array<string, scalar>                                                   $parameters
     * @param array<string, scalar>                                                   $expectedParameters
     */
    public function testGetSuccess(
        callable $suiteCreator,
        array $parameters,
        array $expectedParameters
    ): void {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        $suite = $suiteCreator($apiKey, self::$fileSourceClient, self::$gitSourceClient, self::$suiteClient);

        $serializedSuiteId = (string) new Ulid();
        \assert('' !== $serializedSuiteId);

        self::$serializedSuiteClient->create($apiKey->key, $suite->id, $serializedSuiteId, $parameters);

        $serializedSuite = self::$serializedSuiteClient->get($apiKey->key, $serializedSuiteId);

        self::assertSame($serializedSuiteId, $serializedSuite->id);
        self::assertSame($suite->id, $serializedSuite->suiteId);
        self::assertTrue(in_array(
            $serializedSuite->state,
            ['requested', 'preparing/running', 'preparing/halted', 'failed', 'prepared']
        ));
        self::assertSame($expectedParameters, $serializedSuite->parameters);
    }

    /**
     * @return array<mixed>
     */
    public static function getSuccessDataProvider(): array
    {
        return [
            'file source, no parameters' => [
                'suiteCreator' => function (
                    ApiKey $apiKey,
                    FileSourceClient $fileSourceClient,
                    GitSourceClient $gitSourceClient,
                    SuiteClient $suiteClient
                ) {
                    $source = $fileSourceClient->create($apiKey->key, md5((string) rand()));

                    return $suiteClient->create($apiKey->key, $source->id, md5((string) rand()), []);
                },
                'parameters' => [],
                'expectedParameters' => [],
            ],
            'file source, no valid parameters' => [
                'suiteCreator' => function (
                    ApiKey $apiKey,
                    FileSourceClient $fileSourceClient,
                    GitSourceClient $gitSourceClient,
                    SuiteClient $suiteClient
                ) {
                    $source = $fileSourceClient->create($apiKey->key, md5((string) rand()));

                    return $suiteClient->create($apiKey->key, $source->id, md5((string) rand()), []);
                },
                'parameters' => ['foo' => 'bar'],
                'expectedParameters' => [],
            ],
            'git source, no parameters' => [
                'suiteCreator' => function (
                    ApiKey $apiKey,
                    FileSourceClient $fileSourceClient,
                    GitSourceClient $gitSourceClient,
                    SuiteClient $suiteClient
                ) {
                    $source = $gitSourceClient->create(
                        $apiKey->key,
                        md5((string) rand()),
                        md5((string) rand()),
                        md5((string) rand()),
                        null
                    );

                    return $suiteClient->create($apiKey->key, $source->id, md5((string) rand()), []);
                },
                'parameters' => [],
                'expectedParameters' => [],
            ],
            'git source, has ref parameter' => [
                'suiteCreator' => function (
                    ApiKey $apiKey,
                    FileSourceClient $fileSourceClient,
                    GitSourceClient $gitSourceClient,
                    SuiteClient $suiteClient
                ) {
                    $source = $gitSourceClient->create(
                        $apiKey->key,
                        md5((string) rand()),
                        md5((string) rand()),
                        md5((string) rand()),
                        null
                    );

                    return $suiteClient->create($apiKey->key, $source->id, md5((string) rand()), []);
                },
                'parameters' => [
                    'ref' => 'ref value',
                ],
                'expectedParameters' => [
                    'ref' => 'ref value',
                ],
            ],
        ];
    }
}
