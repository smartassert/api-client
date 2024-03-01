<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\Suite;

use SmartAssert\ApiClient\Data\Source\SourceInterface;
use SmartAssert\ApiClient\Data\Source\Suite;
use SmartAssert\ApiClient\Data\User\ApiKey;
use SmartAssert\ApiClient\Exception\ClientException;
use SmartAssert\ApiClient\Exception\ForbiddenException;
use SmartAssert\ApiClient\Exception\UnauthorizedException;
use SmartAssert\ApiClient\SuiteClient;
use SmartAssert\ApiClient\Tests\Integration\AbstractIntegrationTestCase;
use SmartAssert\ApiClient\Tests\Services\SourcesRepository;
use SmartAssert\ApiClient\Tests\Services\SuitesRepository;
use Symfony\Component\Uid\Ulid;

class ListTest extends AbstractIntegrationTestCase
{
    public function testGetUnauthorized(): void
    {
        $exception = null;

        try {
            self::$suiteClient->list(md5((string) rand()));
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
        \assert('' !== $suiteId);

        try {
            self::$suiteClient->get($apiKey->key, $suiteId);
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
     * @dataProvider listSuccessDataProvider
     *
     * @param callable(ApiKey, ApiKey, SourceInterface[], SourceInterface[], SuiteClient): Suite[] $suitesCreator
     * @param callable(SourceInterface[], Suite[]): Suite[]                                        $expectedCreator
     */
    public function testListSuccess(int $sourceCount, callable $suitesCreator, callable $expectedCreator): void
    {
        $sourcesDataRepository = new SourcesRepository();
        $sourcesDataRepository->removeAllSources();

        $suitesRepository = new SuitesRepository();
        $suitesRepository->removeAllSuites();

        $user1RefreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $user1ApiKey = self::$usersClient->getApiKey($user1RefreshableToken->token);

        $user2RefreshableToken = self::$usersClient->createToken(self::USER2_EMAIL, self::USER2_PASSWORD);
        $user2ApiKey = self::$usersClient->getApiKey($user2RefreshableToken->token);

        $user1Sources = [];
        for ($sourceIndex = 0; $sourceIndex < $sourceCount; ++$sourceIndex) {
            $user1Sources[] = self::$fileSourceClient->create($user1ApiKey->key, md5((string) rand()));
        }

        $user2Sources = [];
        for ($sourceIndex = 0; $sourceIndex < $sourceCount; ++$sourceIndex) {
            $user2Sources[] = self::$fileSourceClient->create($user2ApiKey->key, md5((string) rand()));
        }

        $suites = $suitesCreator($user1ApiKey, $user2ApiKey, $user1Sources, $user2Sources, self::$suiteClient);

        $list = self::$suiteClient->list($user1ApiKey->key);

        self::assertEquals($expectedCreator($user1Sources, $suites), $list);
    }

    /**
     * @return array<mixed>
     */
    public static function listSuccessDataProvider(): array
    {
        return [
            'no sources, no suites' => [
                'sourceCount' => 0,
                'suitesCreator' => function () {
                    return [];
                },
                'expectedCreator' => function () {
                    return [];
                },
            ],
            'single source, single suite, same user' => [
                'sourceCount' => 1,
                'suitesCreator' => function (
                    ApiKey $user1ApiKey,
                    ApiKey $user2ApiKey,
                    array $user1Sources,
                    array $user2Sources,
                    SuiteClient $suiteClient
                ) {
                    $source = $user1Sources[0] ?? null;
                    \assert($source instanceof SourceInterface);

                    return [
                        $suiteClient->create($user1ApiKey->key, $source->getId(), 'suite label 01', []),
                    ];
                },
                'expectedCreator' => function (array $user1Sources, array $suites) {
                    $source = $user1Sources[0] ?? null;
                    \assert($source instanceof SourceInterface);

                    $suite = $suites[0] ?? null;
                    \assert($suite instanceof Suite);

                    return [
                        new Suite($suite->id, $source->getId(), 'suite label 01', [], null),
                    ];
                },
            ],
            'single source, many suites, same user' => [
                'sourceCount' => 1,
                'suitesCreator' => function (
                    ApiKey $user1ApiKey,
                    ApiKey $user2ApiKey,
                    array $user1Sources,
                    array $user2Sources,
                    SuiteClient $suiteClient
                ) {
                    $source = $user1Sources[0] ?? null;
                    \assert($source instanceof SourceInterface);

                    return [
                        $suiteClient->create($user1ApiKey->key, $source->getId(), 'suite label 01', []),
                        $suiteClient->create($user1ApiKey->key, $source->getId(), 'suite label 02', []),
                    ];
                },
                'expectedCreator' => function (array $user1Sources, array $suites) {
                    $source = $user1Sources[0] ?? null;
                    \assert($source instanceof SourceInterface);

                    $suite1 = $suites[0] ?? null;
                    \assert($suite1 instanceof Suite);

                    $suite2 = $suites[1] ?? null;
                    \assert($suite2 instanceof Suite);

                    return [
                        new Suite($suite1->id, $source->getId(), 'suite label 01', [], null),
                        new Suite($suite2->id, $source->getId(), 'suite label 02', [], null),
                    ];
                },
            ],
            'many sources, many suites, different users' => [
                'sourceCount' => 2,
                'suitesCreator' => function (
                    ApiKey $user1ApiKey,
                    ApiKey $user2ApiKey,
                    array $user1Sources,
                    array $user2Sources,
                    SuiteClient $suiteClient
                ) {
                    $source1 = $user1Sources[0] ?? null;
                    \assert($source1 instanceof SourceInterface);

                    $source2 = $user1Sources[1] ?? null;
                    \assert($source2 instanceof SourceInterface);

                    $source3 = $user2Sources[0] ?? null;
                    \assert($source3 instanceof SourceInterface);

                    return [
                        $suiteClient->create($user1ApiKey->key, $source1->getId(), 'suite label 01', []),
                        $suiteClient->create($user2ApiKey->key, $source3->getId(), 'suite label 02', []),
                        $suiteClient->create($user1ApiKey->key, $source2->getId(), 'suite label 03', []),
                    ];
                },
                'expectedCreator' => function (array $user1Sources, array $suites) {
                    $source1 = $user1Sources[0] ?? null;
                    \assert($source1 instanceof SourceInterface);

                    $source2 = $user1Sources[1] ?? null;
                    \assert($source2 instanceof SourceInterface);

                    $suite1 = $suites[0] ?? null;
                    \assert($suite1 instanceof Suite);

                    $suite2 = $suites[2] ?? null;
                    \assert($suite2 instanceof Suite);

                    return [
                        new Suite($suite1->id, $source1->getId(), 'suite label 01', [], null),
                        new Suite($suite2->id, $source2->getId(), 'suite label 03', [], null),
                    ];
                },
            ],
        ];
    }
}
