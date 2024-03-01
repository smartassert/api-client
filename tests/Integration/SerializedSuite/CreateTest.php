<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\SerializedSuite;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\HttpFactory;
use SmartAssert\ApiClient\Data\Source\Suite;
use SmartAssert\ApiClient\Data\User\ApiKey;
use SmartAssert\ApiClient\Exception\ClientException;
use SmartAssert\ApiClient\Exception\Error\Factory as ExceptionFactory;
use SmartAssert\ApiClient\Exception\ForbiddenException;
use SmartAssert\ApiClient\Exception\UnauthorizedException;
use SmartAssert\ApiClient\Factory\Source\SerializedSuiteFactory;
use SmartAssert\ApiClient\FileSourceClient;
use SmartAssert\ApiClient\GitSourceClient;
use SmartAssert\ApiClient\SerializedSuiteClient;
use SmartAssert\ApiClient\ServiceClient\HttpHandler;
use SmartAssert\ApiClient\SuiteClient;
use SmartAssert\ApiClient\Tests\Integration\AbstractIntegrationTestCase;
use Symfony\Component\Uid\Ulid;

class CreateTest extends AbstractIntegrationTestCase
{
    private static SerializedSuiteClient $serializedSuiteClient;

    protected function setUp(): void
    {
        parent::setUp();

        self::$serializedSuiteClient = new SerializedSuiteClient(
            new SerializedSuiteFactory(),
            new HttpHandler(
                new HttpClient(),
                new ExceptionFactory(self::$errorDeserializer),
                new HttpFactory(),
                self::$urlGenerator,
            ),
        );
    }

    public function testCreateUnauthorized(): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        $source = self::$fileSourceClient->create($apiKey->key, md5((string) rand()));
        $suite = self::$suiteClient->create($apiKey->key, $source->id, md5((string) rand()), []);

        $serializedSuiteId = (string) new Ulid();
        \assert('' !== $serializedSuiteId);

        $exception = null;

        try {
            self::$serializedSuiteClient->create(md5((string) rand()), $suite->id, $serializedSuiteId, []);
        } catch (ClientException $exception) {
        }

        self::assertInstanceOf(ClientException::class, $exception);
        self::assertInstanceOf(UnauthorizedException::class, $exception->getInnerException());
    }

    public function testCreateSuiteNotFound(): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        $suiteId = (string) new Ulid();
        \assert('' !== $suiteId);

        $serializedSuiteId = (string) new Ulid();
        \assert('' !== $serializedSuiteId);

        $exception = null;

        try {
            self::$serializedSuiteClient->create($apiKey->key, $suiteId, $serializedSuiteId, []);
        } catch (ClientException $exception) {
        }

        self::assertInstanceOf(ClientException::class, $exception);
        self::assertInstanceOf(ForbiddenException::class, $exception->getInnerException());
    }

    /**
     * @dataProvider createSuccessDataProvider
     *
     * @param callable(ApiKey, FileSourceClient, GitSourceClient, SuiteClient): Suite $suiteCreator
     * @param array<string, scalar>                                                   $parameters
     * @param array<string, scalar>                                                   $expectedParameters
     */
    public function testCreateSuccess(
        callable $suiteCreator,
        array $parameters,
        array $expectedParameters
    ): void {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        $suite = $suiteCreator($apiKey, self::$fileSourceClient, self::$gitSourceClient, self::$suiteClient);

        $serializedSuiteId = (string) new Ulid();
        \assert('' !== $serializedSuiteId);

        $serializedSuite = self::$serializedSuiteClient->create(
            $apiKey->key,
            $suite->id,
            $serializedSuiteId,
            $parameters
        );

        self::assertSame($serializedSuiteId, $serializedSuite->id);
        self::assertSame($suite->id, $serializedSuite->suiteId);
        self::assertSame('requested', $serializedSuite->state);
        self::assertSame($expectedParameters, $serializedSuite->parameters);
    }

    /**
     * @return array<mixed>
     */
    public static function createSuccessDataProvider(): array
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
