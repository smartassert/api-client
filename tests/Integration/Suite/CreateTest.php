<?php

declare(strict_types=1);

namespace SmartAssert\ApiClient\Tests\Integration\Suite;

use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\HttpFactory;
use SmartAssert\ApiClient\Exception\ClientException;
use SmartAssert\ApiClient\Exception\Error\ErrorException;
use SmartAssert\ApiClient\Exception\Error\Factory as ExceptionFactory;
use SmartAssert\ApiClient\Exception\ForbiddenException;
use SmartAssert\ApiClient\Exception\UnauthorizedException;
use SmartAssert\ApiClient\Factory\Source\SuiteFactory;
use SmartAssert\ApiClient\ServiceClient\HttpHandler;
use SmartAssert\ApiClient\SuiteClient;
use SmartAssert\ApiClient\Tests\Integration\AbstractIntegrationTestCase;
use SmartAssert\ServiceRequest\Error\BadRequestError;
use SmartAssert\ServiceRequest\Error\BadRequestErrorInterface;
use SmartAssert\ServiceRequest\Parameter\Parameter;
use SmartAssert\ServiceRequest\Parameter\Requirements;
use SmartAssert\ServiceRequest\Parameter\Size;

class CreateTest extends AbstractIntegrationTestCase
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

    /**
     * @dataProvider createSuiteBadRequestDataProvider
     *
     * @param string[] $tests
     */
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
     * @return array<mixed>
     */
    public static function createSuiteBadRequestDataProvider(): array
    {
        $labelTooLong = str_repeat('.', 256);

        return [
            'missing label' => [
                'label' => '',
                'tests' => [],
                'expected' => new BadRequestError(
                    (new Parameter('label', ''))
                        ->withRequirements(new Requirements('string', new Size(1, 255))),
                    'wrong_size'
                ),
            ],
            'label length exceeds length limit' => [
                'label' => $labelTooLong,
                'tests' => [],
                'expected' => new BadRequestError(
                    (new Parameter('label', $labelTooLong))
                        ->withRequirements(new Requirements('string', new Size(1, 255))),
                    'wrong_size'
                ),
            ],
            'invalid yaml filename within singular tests collection' => [
                'label' => md5((string) rand()),
                'tests' => ['test.txt'],
                'expected' => new BadRequestError(
                    (new Parameter('tests', ['test.txt']))
                        ->withRequirements(new Requirements('yaml_filename_collection'))
                        ->withErrorPosition(1),
                    'invalid'
                ),
            ],
            'invalid yaml filename within tests collection' => [
                'label' => md5((string) rand()),
                'tests' => ['test.yaml', 'test.txt', 'test.yml'],
                'expected' => new BadRequestError(
                    (new Parameter('tests', ['test.yaml', 'test.txt', 'test.yml']))
                        ->withRequirements(new Requirements('yaml_filename_collection'))
                        ->withErrorPosition(2),
                    'invalid'
                ),
            ],
        ];
    }

    /**
     * @dataProvider createSuccessDataProvider
     *
     * @param string[] $tests
     */
    public function testCreateSuccess(string $label, array $tests): void
    {
        $refreshableToken = self::$usersClient->createToken(self::USER1_EMAIL, self::USER1_PASSWORD);
        $apiKey = self::$usersClient->getApiKey($refreshableToken->token);

        $source = self::$fileSourceClient->create($apiKey->key, md5((string) rand()));

        $suite = self::$suiteClient->create($apiKey->key, $source->id, $label, $tests);

        self::assertSame($source->id, $suite->sourceId);
        self::assertEquals($label, $suite->label);
        self::assertEquals(array_unique($tests), $suite->tests);
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
