<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\Suite;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\HttpFactory;
use SmartAssert\ApiClient\Exception\ClientException;
use SmartAssert\ApiClient\Exception\Error\Factory as ExceptionFactory;
use SmartAssert\ApiClient\Exception\ForbiddenException;
use SmartAssert\ApiClient\Exception\UnauthorizedException;
use SmartAssert\ApiClient\Factory\Source\SuiteFactory;
use SmartAssert\ApiClient\ServiceClient\HttpHandler;
use SmartAssert\ApiClient\SuiteClient;
use SmartAssert\ApiClient\Tests\Integration\AbstractIntegrationTestCase;
use Symfony\Component\Uid\Ulid;

class GetTest extends AbstractIntegrationTestCase
{
    private static SuiteClient $suiteClient;

    protected function setUp(): void
    {
        parent::setUp();

        self::$suiteClient = new SuiteClient(
            new SuiteFactory(),
            new HttpHandler(
                new HttpClient(),
                new ExceptionFactory(self::$errorDeserializer),
                new HttpFactory(),
                self::$urlGenerator,
            ),
        );
    }

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
     * @dataProvider getSuccessDataProvider
     *
     * @param string[] $tests
     */
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
